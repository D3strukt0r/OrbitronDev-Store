<?php

namespace App\Controller;

use App\Entity\DeliveryType;
use App\Entity\Product;
use App\Entity\ProductRating;
use App\Entity\Store;
use App\Entity\StorePaymentMethods;
use App\Entity\Voucher;
use App\Form\AddCommentType;
use App\Form\AddToCartType;
use App\Form\CheckoutType;
use App\Form\NewStoreType;
use App\Service\AdminControlPanel;
use App\Service\CartHelper;
use Braintree\Gateway;
use Doctrine\Common\Persistence\ObjectManager;
use OrbitronDev\OAuth2\Client\Provider\OrbitronDevResourceOwner;
use Swift_Image;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DefaultController extends Controller
{
    private $cachedData;
    private $dataWasLoaded = false;

    /**
     * @param bool $updateCache
     *
     * @return \OrbitronDev\OAuth2\Client\Provider\OrbitronDevResourceOwner
     */
    private function getDataFromToken($updateCache = false): OrbitronDevResourceOwner
    {
        if (!$updateCache && is_array($this->cachedData)) {
            return $this->cachedData;
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        /** @var \League\OAuth2\Client\Token\AccessToken $accessToken */
        $accessToken = unserialize($user->getTokenData());

        $registry = $this->get('oauth2.registry');
        $client = $registry->getClient('orbitrondev');
        // access the underlying "provider" from league/oauth2-client
        $provider = $client->getOAuth2Provider();

        if ($accessToken->hasExpired()) {
            $accessToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $accessToken->getRefreshToken(),
            ]);

            // Purge old access token and store new access token to your data store.
            $user->setTokenData(serialize($accessToken));
            $this->getDoctrine()->getManager()->flush();
        }

        // get access token and then user
        $resourceOwner = $client->fetchUserFromToken($accessToken);
        $this->dataWasLoaded = true;
        $this->cachedData = $resourceOwner;

        return $resourceOwner;
    }

    private function askForPermission(array $scopes)
    {
        $registry = $this->get('oauth2.registry');
        $client = $registry->getClient('orbitrondev');

        return $client->redirect($scopes);
    }

    private function hasAccessToData($data)
    {
        if (!$this->dataWasLoaded) {
            $data = $this->getDataFromToken();
        }

        return array_key_exists($data, $this->cachedData);
    }

    //////////////////////////////////////////////////

    public function index(ObjectManager $em)
    {
        /** @var \App\Entity\Store[] $storeList */
        $storeList = $em->getRepository(Store::class)->findAll();

        return $this->render('list-stores.html.twig', [
            'store_list' => $storeList,
        ]);
    }

    public function newStore(ObjectManager $em, Request $request, TranslatorInterface $translator)
    {
        //////////// TEST IF USER IS LOGGED IN ////////////
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }
        //////////// END TEST IF USER IS LOGGED IN ////////////

        $createStoreForm = $this->createForm(NewStoreType::class);

        $createStoreForm->handleRequest($request);
        if ($createStoreForm->isSubmitted() && $createStoreForm->isValid()) {
            $formData = $createStoreForm->getData();

            try {
                $newStore = new Store();
                $newStore
                    ->setName($formData['name'])
                    ->setUrl($formData['url'])
                    ->setEmail($formData['email'])
                    ->setOwner($user)
                    ->setCreated(new \DateTime())
                    ->setDefaultLanguage($formData['language'])
                    ->setDefaultCurrency($formData['currency']);
                $em->persist($newStore);
                $em->flush();

                return $this->redirectToRoute('store_index', ['store' => $newStore->getUrl()]);
            } catch (\Exception $e) {
                $createStoreForm->addError(new FormError($translator->trans('new_store.not_created', ['%error_message%' => $e->getMessage()], 'validators')));
            }
        }

        return $this->render('create-new-store.html.twig', [
            'create_store_form' => $createStoreForm->createView(),
        ]);
    }

    public function storeIndex(ObjectManager $em, CartHelper $cartHelper, $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        // Load list of all products
        /** @var \App\Entity\Product[] $list */
        $list = $em->getRepository(Product::class)->findBy(['store' => $store], ['last_edited' => 'DESC']);

        // Shopping cart widget
        $cartHelper->initialise($store, $user);
        $cart = $cartHelper->getCart(true, true);

        return $this->render('theme1/index.html.twig', [
            'current_store' => $store,
            'product_list' => $list,
            'cart' => $cart,
        ]);
    }

    public function storeProduct(ObjectManager $em, Request $request, CartHelper $cartHelper, $store, $product)
    {
        //////////// TEST IF STORE EXISTS ////////////
        /** @var null|\App\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        //////////// TEST IF PRODUCT EXISTS ////////////
        /** @var null|\App\Entity\Product $product */
        $product = $em->getRepository(Product::class)->findOneBy(['id' => $product]);
        if (null === $product) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF PRODUCT EXISTS ////////////

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        $cartHelper->initialise($store, $user);

        // Add product to cart
        $addToCartForm = $this->createForm(AddToCartType::class, null, ['disable_fields' => 0 === $product->getStock() ? true : false]);
        $addToCartForm->handleRequest($request);
        if ($addToCartForm->isSubmitted() && $addToCartForm->isValid()) {
            $formData = $addToCartForm->getData();

            $cartHelper->addToCart($product, $formData['product_count']);

            $this->addFlash('product_added', 'Your product was added to the cart'); // TODO: Missing translation
        }

        // Add a review
        $addCommentForm = $this->createForm(AddCommentType::class);
        $addCommentForm->handleRequest($request);
        if ($addCommentForm->isSubmitted() && $addCommentForm->isValid()) {
            $formData = $addCommentForm->getData();

            $stars = $formData['rating'];
            $comment = $formData['comment'];

            if ($stars > 5) {
                $stars = 5;
            }

            $rating = new ProductRating();
            $rating
                ->setProduct($product)
                ->setUser($user)
                ->setRating($stars)
                ->setComment($comment)
                ->setCreatedOn(new \DateTime())
                ->setUpdatedOn(new \DateTime());
            $em->persist($rating);

            // Update rating count
            $rating = $product->getRatingCount();
            $rating = $rating + 1;
            $product->setRatingCount($rating);

            // Update stars
            $totalStars = 0;
            $count = 0;
            foreach ($product->getRatings() as $item) {
                $totalStars += $item->getRating();
                ++$count;
            }
            $totalStars += $stars;
            ++$count;

            $average = round(($totalStars / $count) * 2) / 2;
            $product->setRatingAverage($average);
            $em->flush();
        }

        /** @var \App\Entity\ProductRating[] $comments */
        $comments = $em->getRepository(ProductRating::class)->findBy(['product' => $product], ['updated_on' => 'DESC']);

        // Shopping cart widget
        $cart = $cartHelper->getCart(true, true);

        return $this->render('theme1/product.html.twig', [
            'current_store' => $store,
            'current_product' => $product,
            'comments' => $comments,
            'add_to_cart_form' => $addToCartForm->createView(),
            'add_comment_form' => $addCommentForm->createView(),
            'cart' => $cart,
        ]);
    }

    public function storeCheckout(ObjectManager $em, Request $request, CartHelper $cartHelper, $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        $cartHelper->initialise($store, $user);
        $cart = $cartHelper->getCart(true, false);

        $userData = $this->getDataFromToken(true);
        $activeAddress = null !== $userData ? $userData->getActiveAddress() : null;
        $checkoutFormData = [
            'name' => (null !== $userData ? $userData->getFirstName() : '').' '.(null !== $userData ? $userData->getSurname() : ''),
            'email' => null !== $userData ? $userData->getEmail() : '',
            'location_street' => null !== $activeAddress ? $activeAddress['street'] : '',
            'location_street_number' => null !== $activeAddress ? $activeAddress['house_number'] : '',
            'location_postal_code' => null !== $activeAddress ? $activeAddress['zip_code'] : '',
            'location_city' => null !== $activeAddress ? $activeAddress['city'] : '',
            'location_country' => null !== $activeAddress ? $activeAddress['country'] : '',
        ];
        $checkoutForm = $this->createForm(CheckoutType::class, null, $checkoutFormData);

        /** @var \App\Entity\StorePaymentMethods[] $paymentTypes */
        $paymentTypes = $em->getRepository(StorePaymentMethods::class)->findBy(['store' => $store]);

        $payment = null;
        if (count($paymentTypes) > 0) {
            foreach ($paymentTypes as $type) {
                if ($type->getId() !== $store->getActivePaymentMethod()) {
                    continue;
                }
                $payment = [];
                $payment['method'] = $type;
                if (StorePaymentMethods::TYPE_BRAINTREE_PRODUCTION === $type->getType()) {
                    $payment['gateway'] = new Gateway([
                        'environment' => 'production',
                        'merchantId' => $type->getData()['merchant_id'],
                        'publicKey' => $type->getData()['public_key'],
                        'privateKey' => $type->getData()['private_key'],
                    ]);
                    $payment['client_token'] = $payment['gateway']->clientToken()->generate();
                } elseif (StorePaymentMethods::TYPE_BRAINTREE_SANDBOX === $type->getType()) {
                    $payment['gateway'] = new Gateway([
                        'environment' => 'sandbox',
                        'merchantId' => $type->getData()['merchant_id'],
                        'publicKey' => $type->getData()['public_key'],
                        'privateKey' => $type->getData()['private_key'],
                    ]);
                    $payment['client_token'] = $payment['gateway']->clientToken()->generate();
                }
            }
        }

        $checkoutForm->handleRequest($request);
        if ($checkoutForm->isSubmitted() && $checkoutForm->isValid()) {
            $formData = $checkoutForm->getData();

            // TODO: Cancel if no payment method given

            $nonceFromTheClient = $request->request->get('payment_method_nonce');

            $productUnavailable = [];
            $newProductsStock = [];
            foreach ($cart as $key => $productInfo) {
                /** @var \App\Entity\Product $product */
                $product = $em->getRepository(Product::class)->findOneBy(['id' => $productInfo['id']]);

                if ($product->getStock() >= $productInfo['in_cart']) {
                    $newProductsStock[$product->getId()] = $product->getStock() - $productInfo['in_cart'];
                } else {
                    $productData = $product->toArray();
                    $productData['count'] = $productInfo['in_cart'];
                    $productUnavailable[$key] = $productData;
                }
            }

            if (count($productUnavailable) > 0) {
                foreach ($productUnavailable as $key => $item) {
                    $this->addFlash('products_unavailable', $item['name'].' has only '.$item['stock_available'].' left! You wanted '.$item['count']); // TODO: Missing translation
                }
            } else {
                $cart2 = $cartHelper->getCart(true, true);
                $paymentSuccessful = false;

                if (StorePaymentMethods::TYPE_BRAINTREE_PRODUCTION === $payment['method']->getType() || StorePaymentMethods::TYPE_BRAINTREE_SANDBOX === $payment['method']->getType()) {
                    $result = $payment['gateway']->transaction()->sale([
                        'amount' => $cart2['system']['total_price'],
                        'paymentMethodNonce' => $nonceFromTheClient,
                        'options' => [
                            'submitForSettlement' => true,
                        ],
                    ]);
                    if (true === $result->success) {
                        $paymentSuccessful = true;
                    }
                }

                if ($paymentSuccessful) {
                    $message = (new Swift_Message())
                        ->setSubject('Order confirmation')
                        ->setFrom([$store->getEmail() => $store->getName()])
                        ->setTo([trim($formData['email']) => trim($formData['name'])])
                        ->setBcc([$store->getEmail()])
                        ->setReplyTo([$store->getEmail() => $store->getName()]);

                    $imgDir1 = $message->embed(Swift_Image::fromPath($this->get('kernel')->getProjectDir().'/web/img/logo-long.png'));
                    $message->setBody($this->renderView('mail/order-confirmation.html.twig', [
                            'current_store' => $store,
                            'order_form' => $formData,
                            'header_image' => $imgDir1,
                            'ordered_time' => time(),
                            'cart' => $cart,
                        ]), 'text/html');

                    /** @var \Swift_Mailer $mailer */
                    $mailer = $this->get('mailer');
                    $mailSent = $mailer->send($message);

                    if ($mailSent) {
                        // TODO: Integrate shipping method into the form
                        $formData['delivery_type'] = $request->request->get('shipping');
                        $cartHelper->makeOrder($formData);
                        $cartHelper->clearCart();
                        $cart = $cartHelper->getCart(true, true);

                        $this->addFlash('order_sent', 'We saved your order in our system, and sent you a confirmation. We will deliver you the products as soon as possible.'); // TODO: Missing translation
                    } else {
                        $this->addFlash('order_not_sent', 'Your order was not sent. Try again!'); // TODO: Missing translation
                    }
                } else {
                    $this->addFlash('order_not_sent', 'It looks like, we couldn\'t create a transaction with your given credit card information. Try again!'); // TODO: Missing translation
                }
            }
        }

        /** @var \App\Entity\DeliveryType[] $deliveryType */
        $deliveryType = $em->getRepository(DeliveryType::class)->findBy(['store' => $store]);

        return $this->render('theme1/checkout.html.twig', [
            'current_store' => $store,
            'checkout_form' => $checkoutForm->createView(),
            'delivery_types' => $deliveryType,
            'cart' => $cart,
            'payment' => $payment,
        ]);
    }

    public function storeDoCheckVoucher(ObjectManager $em, Request $request, $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var \App\Entity\Voucher $voucher */
        $voucher = $em->getRepository(Voucher::class)->findOneBy(['store' => $store, 'code' => $request->query->get('code')]);

        $result = new \SimpleXMLElement('<root></root>');
        if (null === $voucher) {
            $result->addChild('result', 'invalid');
        } else {
            $result->addChild('result', 'valid');
            $result->addChild('type', $voucher->getType());
            $result->addChild('amount', $voucher->getAmount());
        }
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');
        $response->setContent($result->asXML());

        return $response;
    }

    public function storeDoClearCart(ObjectManager $em, CartHelper $cartHelper, $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        $cartHelper->initialise($store, $user);
        $cartHelper->clearCart();

        return '';
    }

    public function storeDoAddToCart(ObjectManager $em, Request $request, CartHelper $cartHelper, $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        $cartHelper->initialise($store, $user);

        $product = $request->get('product', null);
        $count = $request->get('product_count', null);
        $responseType = $request->get('response', null);
        $cartHelper->addToCart($product, $count);

        $browser = $request->get('browser', null);
        if (null !== $responseType) {
            if ('json' === $responseType) {
                return $this->json([
                    'result' => 'true',
                ]);
            }
        }
        if (null === $browser || (null !== $browser && true === $browser)) {
            return $this->redirect($request->server->get('HTTP_REFERER'), 302);
        }

        return '';
    }

    public function storeDoRemoveFromCart(ObjectManager $em, Request $request, CartHelper $cartHelper, $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Entity\Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        $cartHelper->initialise($store, $user);

        $product = $request->get('product', null);
        $count = $request->get('product_count', null);
        $responseType = $request->get('response', null);
        $cartHelper->removeFromCart($product, $count);

        $browser = $request->get('browser', null);
        if (null !== $responseType) {
            if ('json' === $responseType) {
                return $this->json([
                    'result' => 'true',
                ]);
            }
        }
        if (null === $browser || (null !== $browser && true === $browser)) {
            return $this->redirect($request->server->get('HTTP_REFERER'), 302);
        }

        return '';
    }

    public function storeAdmin(ObjectManager $em, Request $request, $store, $page)
    {
        //////////// TEST IF USER IS LOGGED IN ////////////
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }
        //////////// END TEST IF USER IS LOGGED IN ////////////

        //////////// TEST IF STORE EXISTS ////////////
        /** @var \App\Entity\Store|null $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        if ($user->getId() !== $store->getOwner()->getId()) {
            throw $this->createAccessDeniedException();
        }

        AdminControlPanel::loadLibs($this->get('kernel')->getProjectDir(), $this->container);

        $navigationLinks = AdminControlPanel::getTree();

        $view = 'DefaultController::notFound';

        $list = AdminControlPanel::getFlatTree();

        $key = null;
        while ($item = current($list)) {
            if (isset($item['href']) && $item['href'] === $page) {
                $key = key($list);
            }
            next($list);
        }

        if (null !== $key) {
            if (is_callable('\\App\\Controller\\Panel\\'.$list[$key]['view'])) {
                $view = $list[$key]['view'];
            }
        }
        $response = $this->forward('App\\Controller\\Panel\\'.$view, [
            'navigation' => $navigationLinks,
            'request' => $request,
            'store' => $store,
        ]);

        return $response;
    }
}
