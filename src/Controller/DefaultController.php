<?php

namespace App\Controller;

use App\Entity\DeliveryType;
use App\Entity\Product;
use App\Entity\ProductRating;
use App\Entity\Store;
use App\Entity\StorePaymentMethods;
use App\Entity\User;
use App\Entity\Voucher;
use App\Form\AddCommentType;
use App\Form\AddToCartType;
use App\Form\CheckoutType;
use App\Form\NewStoreType;
use App\Service\AdminControlPanel;
use App\Service\CartHelper;
use Braintree\Gateway;
use D3strukt0r\OAuth2\Client\Provider\Generation2ResourceOwner;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultController extends AbstractController
{
    private $cachedData;
    private $dataWasLoaded = false;

    /**
     * @Route("/", name="index")
     *
     * @return Response
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Store[] $storeList */
        $storeList = $em->getRepository(Store::class)->findAll();

        return $this->render(
            'list-stores.html.twig',
            [
                'store_list' => $storeList,
            ]
        );
    }

    /**
     * @Route("/new-store", name="new")
     *
     * @param Request             $request    The request
     * @param TranslatorInterface $translator The translator
     *
     * @return RedirectResponse|Response
     */
    public function newStore(Request $request, TranslatorInterface $translator)
    {
        //////////// TEST IF USER IS LOGGED IN ////////////
        /** @var User|null $user */
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
                    ->setDefaultCurrency($formData['currency'])
                ;
                $em = $this->getDoctrine()->getManager();
                $em->persist($newStore);
                $em->flush();

                return $this->redirectToRoute('store_index', ['store' => $newStore->getUrl()]);
            } catch (\Exception $e) {
                $createStoreForm->addError(
                    new FormError(
                        $translator->trans(
                            'new_store.not_created',
                            ['%error_message%' => $e->getMessage()],
                            'validators'
                        )
                    )
                );
            }
        }

        return $this->render(
            'create-new-store.html.twig',
            [
                'create_store_form' => $createStoreForm->createView(),
            ]
        );
    }

    /**
     * @Route("/{store}", name="store_index")
     *
     * @param CartHelper $cartHelper The cart helper
     * @param string     $store      The store
     *
     * @return Response
     */
    public function storeIndex(CartHelper $cartHelper, string $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        $em = $this->getDoctrine()->getManager();
        /** @var Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var User|null $user */
        $user = $this->getUser();

        // Load list of all products
        /** @var Product[] $list */
        $list = $em->getRepository(Product::class)->findBy(['store' => $store], ['last_edited' => 'DESC']);

        // Shopping cart widget
        $cartHelper->initialise($store, $user);
        $cart = $cartHelper->getCart(true, true);

        return $this->render(
            'theme1/index.html.twig',
            [
                'current_store' => $store,
                'product_list' => $list,
                'cart' => $cart,
            ]
        );
    }

    /**
     * @Route("/{store}/product/{product}", name="store_product")
     *
     * @param Request    $request    The request
     * @param CartHelper $cartHelper The cart helper
     * @param string     $store      The store
     * @param string     $product    The product
     *
     * @return Response
     */
    public function storeProduct(Request $request, CartHelper $cartHelper, string $store, string $product)
    {
        //////////// TEST IF STORE EXISTS ////////////
        $em = $this->getDoctrine()->getManager();
        /** @var Store|null $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        //////////// TEST IF PRODUCT EXISTS ////////////
        /** @var Product|null $product */
        $product = $em->getRepository(Product::class)->findOneBy(['id' => $product]);
        if (null === $product) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF PRODUCT EXISTS ////////////

        /** @var User|null $user */
        $user = $this->getUser();

        $cartHelper->initialise($store, $user);

        // Add product to cart
        $addToCartForm = $this->createForm(
            AddToCartType::class,
            null,
            ['disable_fields' => 0 === $product->getStock() ? true : false]
        );
        $addToCartForm->handleRequest($request);
        if ($addToCartForm->isSubmitted() && $addToCartForm->isValid()) {
            $formData = $addToCartForm->getData();

            $cartHelper->addToCart($product, $formData['product_count']);

            $this->addFlash('product_added', 'service_product.messages.product_added');
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
                ->setUpdatedOn(new \DateTime())
            ;
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

        /** @var ProductRating[] $comments */
        $comments = $em->getRepository(ProductRating::class)->findBy(['product' => $product], ['updated_on' => 'DESC']);

        // Shopping cart widget
        $cart = $cartHelper->getCart(true, true);

        return $this->render(
            'theme1/product.html.twig',
            [
                'current_store' => $store,
                'current_product' => $product,
                'comments' => $comments,
                'add_to_cart_form' => $addToCartForm->createView(),
                'add_comment_form' => $addCommentForm->createView(),
                'cart' => $cart,
            ]
        );
    }

    /**
     * @Route("/{store}/checkout", name="store_checkout")
     *
     * @param MailerInterface $mailer     The mailer
     * @param Request         $request    The request
     * @param CartHelper      $cartHelper The cart helper
     * @param string          $store      The store
     *
     * @throws IdentityProviderException
     *
     * @return Response
     */
    public function storeCheckout(MailerInterface $mailer, Request $request, CartHelper $cartHelper, string $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        $em = $this->getDoctrine()->getManager();
        /** @var Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var User|null $user */
        $user = $this->getUser();

        $cartHelper->initialise($store, $user);
        $cart = $cartHelper->getCart(true, false);

        $checkoutFormData = [];
        if ($user) {
            $userData = $this->getDataFromToken(true);
            $activeAddress = null !== $userData ? $userData->getActiveAddress() : null;
            $checkoutFormData = [
                'name' => (null !== $userData ? $userData->getFirstName(
                    ) : '').' '.(null !== $userData ? $userData->getSurname() : ''),
                'email' => null !== $userData ? $userData->getEmail() : '',
                'location_street' => null !== $activeAddress ? $activeAddress['street'] : '',
                'location_street_number' => null !== $activeAddress ? $activeAddress['house_number'] : '',
                'location_postal_code' => null !== $activeAddress ? $activeAddress['zip_code'] : '',
                'location_city' => null !== $activeAddress ? $activeAddress['city'] : '',
                'location_country' => null !== $activeAddress ? $activeAddress['country'] : '',
            ];
        }
        $checkoutForm = $this->createForm(CheckoutType::class, null, $checkoutFormData);

        /** @var StorePaymentMethods[] $paymentTypes */
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
                    $payment['gateway'] = new Gateway(
                        [
                            'environment' => 'production',
                            'merchantId' => $type->getData()['merchant_id'],
                            'publicKey' => $type->getData()['public_key'],
                            'privateKey' => $type->getData()['private_key'],
                        ]
                    );
                    $payment['client_token'] = $payment['gateway']->clientToken()->generate();
                } elseif (StorePaymentMethods::TYPE_BRAINTREE_SANDBOX === $type->getType()) {
                    $payment['gateway'] = new Gateway(
                        [
                            'environment' => 'sandbox',
                            'merchantId' => $type->getData()['merchant_id'],
                            'publicKey' => $type->getData()['public_key'],
                            'privateKey' => $type->getData()['private_key'],
                        ]
                    );
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
                /** @var Product $product */
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
                    $this->addFlash(
                        'products_unavailable',
                        $item['name'].' has only '.$item['stock_available'].' left! You wanted '.$item['count']
                    ); // TODO: Missing translation
                }
            } else {
                $cart2 = $cartHelper->getCart(true, true);
                $paymentSuccessful = false;

                if (StorePaymentMethods::TYPE_BRAINTREE_PRODUCTION === $payment['method']->getType(
                    ) || StorePaymentMethods::TYPE_BRAINTREE_SANDBOX === $payment['method']->getType()) {
                    $result = $payment['gateway']->transaction()->sale(
                        [
                            'amount' => $cart2['system']['total_price'],
                            'paymentMethodNonce' => $nonceFromTheClient,
                            'options' => [
                                'submitForSettlement' => true,
                            ],
                        ]
                    )
                    ;
                    if (true === $result->success) {
                        $paymentSuccessful = true;
                    }
                }

                if ($paymentSuccessful) {
                    $message = (new Email())
                        ->from(new Address($store->getEmail(), $store->getName()))
                        ->to(new Address(trim($formData['email']), trim($formData['name'])))
                        ->bcc(new Address($store->getEmail()))
                        ->replyTo(new Address($store->getEmail(), $store->getName()))
                        ->subject('Order confirmation')
                        ->html(
                            $this->renderView(
                                'mail/order-confirmation.html.twig',
                                [
                                    'current_store' => $store,
                                    'order_form' => $formData,
                                    'ordered_time' => time(),
                                    'cart' => $cart,
                                ]
                            )
                        )
                    ;

                    try {
                        $mailer->send($message);

                        // TODO: Integrate shipping method into the form
                        $formData['delivery_type'] = $request->request->get('shipping');
                        $cartHelper->makeOrder($formData);
                        $cartHelper->clearCart();
                        $cart = $cartHelper->getCart(true, true);

                        $this->addFlash('order_sent', 'service_checkout.messages.order_sent');
                    } catch (TransportExceptionInterface $e) {
                        $this->addFlash('order_not_sent', 'service_checkout.messages.order_sent_no_mail');
                    }
                } else {
                    $this->addFlash('order_not_sent', 'service_checkout.messages.order_not_sent');
                }
            }
        }

        /** @var DeliveryType[] $deliveryType */
        $deliveryType = $em->getRepository(DeliveryType::class)->findBy(['store' => $store]);

        return $this->render(
            'theme1/checkout.html.twig',
            [
                'current_store' => $store,
                'checkout_form' => $checkoutForm->createView(),
                'delivery_types' => $deliveryType,
                'cart' => $cart,
                'payment' => $payment,
            ]
        );
    }

    /**
     * @Route("/{store}/do-check-voucher", name="store_do_check_voucher")
     *
     * @param Request $request The request
     * @param string  $store   The store
     *
     * @return JsonResponse
     */
    public function storeDoCheckVoucher(Request $request, string $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        $em = $this->getDoctrine()->getManager();
        /** @var Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var Voucher $voucher */
        $voucher = $em->getRepository(Voucher::class)->findOneBy(
            ['store' => $store, 'code' => $request->query->get('code')]
        )
        ;

        if (null === $voucher) {
            return $this->json(
                [
                    'result' => 'invalid',
                ]
            );
        }

        return $this->json(
            [
                'result' => 'valid',
                'code' => $voucher->getCode(),
                'type' => $voucher->getType(),
                'amount' => $voucher->getAmount(),
            ]
        );
    }

    /**
     * @Route("/{store}/do-clear-cart", name="store_do_clear_cart")
     *
     * @param Request    $request    The request
     * @param CartHelper $cartHelper The cart helper
     * @param string     $store      The store
     *
     * @return JsonResponse|RedirectResponse|Response
     */
    public function storeDoClearCart(Request $request, CartHelper $cartHelper, string $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        $em = $this->getDoctrine()->getManager();
        /** @var Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var User|null $user */
        $user = $this->getUser();

        $cartHelper->initialise($store, $user);
        $cartHelper->clearCart();

        $responseType = $request->get('response', null);
        $browser = $request->get('browser', null);
        if (null !== $responseType) {
            if ('json' === $responseType) {
                return $this->json(
                    [
                        'result' => 'true',
                    ]
                );
            }
        }
        if (null === $browser || (null !== $browser && true === $browser)) {
            return $this->redirect($request->server->get('HTTP_REFERER'), 302);
        }

        return new Response();
    }

    public function storeDoAddToCart(Request $request, CartHelper $cartHelper, string $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        $em = $this->getDoctrine()->getManager();
        /** @var Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var User|null $user */
        $user = $this->getUser();

        $cartHelper->initialise($store, $user);

        $product = $request->get('product', null);
        $count = $request->get('product_count', null);
        $responseType = $request->get('response', null);
        $cartHelper->addToCart($product, $count);

        $browser = $request->get('browser', null);
        if (null !== $responseType) {
            if ('json' === $responseType) {
                return $this->json(
                    [
                        'result' => 'true',
                    ]
                );
            }
        }
        if (null === $browser || (null !== $browser && true === $browser)) {
            return $this->redirect($request->server->get('HTTP_REFERER'), 302);
        }

        return new Response();
    }

    /**
     * @Route("/{store}/do-remove-from-cart", name="store_do_remove_from_cart")
     *
     * @param Request    $request    The request
     * @param CartHelper $cartHelper The cart helper
     * @param string     $store      The store
     *
     * @return string|JsonResponse|RedirectResponse
     */
    public function storeDoRemoveFromCart(Request $request, CartHelper $cartHelper, string $store)
    {
        //////////// TEST IF STORE EXISTS ////////////
        $em = $this->getDoctrine()->getManager();
        /** @var Store $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        /** @var User|null $user */
        $user = $this->getUser();

        $cartHelper->initialise($store, $user);

        $product = $request->get('product', null);
        $count = $request->get('product_count', null);
        $responseType = $request->get('response', null);
        $cartHelper->removeFromCart($product, $count);

        $browser = $request->get('browser', null);
        if (null !== $responseType) {
            if ('json' === $responseType) {
                return $this->json(
                    [
                        'result' => 'true',
                    ]
                );
            }
        }
        if (null === $browser || (null !== $browser && true === $browser)) {
            return $this->redirect($request->server->get('HTTP_REFERER'), 302);
        }

        return '';
    }

    /**
     * @Route("/{store}/admin/{page}", name="store_admin")
     *
     * @param KernelInterface       $kernel       The kernel
     * @param TokenStorageInterface $tokenStorage The token storage
     * @param Request               $request      The request
     * @param string                $store        The store
     * @param string                $page         The page
     *
     * @return Response
     */
    public function storeAdmin(
        KernelInterface $kernel,
        TokenStorageInterface $tokenStorage,
        Request $request,
        string $store,
        string $page
    ) {
        //////////// TEST IF USER IS LOGGED IN ////////////
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException();
        }
        //////////// END TEST IF USER IS LOGGED IN ////////////

        //////////// TEST IF STORE EXISTS ////////////
        $em = $this->getDoctrine()->getManager();
        /** @var Store|null $store */
        $store = $em->getRepository(Store::class)->findOneBy(['url' => $store]);
        if (null === $store) {
            throw $this->createNotFoundException();
        }
        //////////// END TEST IF STORE EXISTS ////////////

        if ($user->getId() !== $store->getOwner()->getId()) {
            throw $this->createAccessDeniedException();
        }

        AdminControlPanel::loadLibs($kernel->getProjectDir(), $tokenStorage);

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

        return $this->forward(
            'App\\Controller\\Panel\\'.$view,
            [
                'navigation' => $navigationLinks,
                'request' => $request,
                'store' => $store,
            ]
        );
    }

    /**
     * @param bool $updateCache
     *
     * @throws IdentityProviderException
     *
     * @return Generation2ResourceOwner The resources
     */
    private function getDataFromToken($updateCache = false): Generation2ResourceOwner
    {
        if (!$updateCache && is_array($this->cachedData)) {
            return $this->cachedData;
        }

        /** @var User $user */
        $user = $this->getUser();
        /** @var AccessToken $accessToken */
        $accessToken = unserialize($user->getTokenData());

        $registry = $this->get('oauth2.registry');
        $client = $registry->getClient('generation2');
        // access the underlying "provider" from league/oauth2-client
        $provider = $client->getOAuth2Provider();

        if ($accessToken->hasExpired()) {
            $accessToken = $provider->getAccessToken(
                'refresh_token',
                [
                    'refresh_token' => $accessToken->getRefreshToken(),
                ]
            );

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
        $client = $registry->getClient('generation2');

        return $client->redirect($scopes);
    }

    private function hasAccessToData($data)
    {
        if (!$this->dataWasLoaded) {
            $data = $this->getDataFromToken();
        }

        return array_key_exists($data, $this->cachedData);
    }
}
