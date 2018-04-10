<?php

namespace App\Controller\Panel;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Voucher;
use App\Form\AddProductType;
use App\Form\AddVoucherType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ShopController extends Controller
{
    public static function __setupNavigation()
    {
        return [
            [
                'type' => 'group',
                'parent' => 'root',
                'id' => 'shop',
                'title' => 'Shop',
                'icon' => 'hs-admin-shopping-cart',
            ],
            [
                'type' => 'link',
                'parent' => 'shop',
                'id' => 'catalogue',
                'title' => 'Catalogue/Products',
                'href' => 'catalogue',
                'view' => 'ShopController::catalogue',
            ],
            [
                'type' => 'link',
                'parent' => 'null',
                'id' => 'create_product',
                'title' => 'Create product',
                'href' => 'create-product',
                'view' => 'ShopController::createProduct',
            ],
            [
                'type' => 'link',
                'parent' => 'shop',
                'id' => 'orders',
                'title' => 'Orders',
                'href' => 'orders',
                'view' => 'ShopController::orders',
            ],
            [
                'type' => 'link',
                'parent' => 'null',
                'id' => 'state_1',
                'title' => 'Change to sent',
                'href' => 'change_order_statement_to_1',
                'view' => 'ShopController::changeOrderStatusTo1',
            ],
            [
                'type' => 'link',
                'parent' => 'null',
                'id' => 'state_2',
                'title' => 'Change to Processed',
                'href' => 'change_order_statement_to_2',
                'view' => 'ShopController::changeOrderStatusTo2',
            ],
            [
                'type' => 'link',
                'parent' => 'shop',
                'id' => 'vouchers',
                'title' => 'Vouchers',
                'href' => 'vouchers',
                'view' => 'ShopController::vouchers',
            ],
            [
                'type' => 'link',
                'parent' => 'null',
                'id' => 'create_voucher',
                'title' => 'Create voucher',
                'href' => 'create-voucher',
                'view' => 'ShopController::createVoucher',
            ],
        ];
    }

    public static function __callNumber()
    {
        return 20;
    }

    public function catalogue(ObjectManager $em, Request $request, $navigation, $store)
    {
        /** @var \App\Entity\Product[] $productList */
        $productList = $em->getRepository(Product::class)->findBy(['store' => $store], ['last_edited' => 'DESC']);

        $userLanguage = null !== $request->query->get('lang') ? $request->query->get('lang') : 'en';
        $userCurrency = null !== $request->query->get('currency') ? $request->query->get('currency') : 'USD';

        return $this->render('theme_admin1/catalogue.html.twig', [
            'navigation_links' => $navigation,
            'current_store' => $store,
            'products' => $productList,
            'language' => $userLanguage,
            'currency' => $userCurrency,
        ]);
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $em
     * @param \Symfony\Component\HttpFoundation\Request  $request
     * @param                                            $navigation
     * @param \App\Entity\Store                          $store
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createProduct(ObjectManager $em, Request $request, $navigation, $store)
    {
        $addProductForm = $this->createForm(AddProductType::class);
        $addProductForm->handleRequest($request);
        if ($addProductForm->isSubmitted() && $addProductForm->isValid()) {
            $formData = $addProductForm->getData();

            $product = (new Product())
                ->setStore($store)
                ->setOwner($this->getUser())
                ->setName($formData['name'], $store->getDefaultLanguage())
                ->setDescription($formData['description'], $store->getDefaultLanguage())
                ->setShortDescription($formData['short_description'], $store->getDefaultLanguage())
                ->setPrice((float) ($formData['price']), $store->getDefaultCurrency())
                ->setStock((int) ($formData['stock']));
            if (null !== $formData['sale_price']) {
                $product->setSalePrice((float) ($formData['sale_price']), $store->getDefaultCurrency());
            }
            $em->persist($product);
            $em->flush();

            header('Location: '.$this->generateUrl('store_admin', ['store' => $store->getUrl(), 'page' => 'catalogue']));
            exit;
        }

        return $this->render('theme_admin1/create_product.html.twig', [
            'navigation_links' => $navigation,
            'current_store' => $store,
            'add_product_form' => $addProductForm->createView(),
        ]);
    }

    public function orders(ObjectManager $em, $navigation, $store)
    {
        $userLanguage = 'en'; // TODO: Make this editable by the user
        $userCurrency = 'USD';  // TODO: Make this editable by the user

        /** @var \App\Entity\Order[] $orders */
        $orders = $em->getRepository(Order::class)->findBy(['store' => $store]);
        $ordersData = [];

        foreach ($orders as $index => $order) {
            // Format product list
            $productList = [];
            foreach ($order->getProductList() as $key => $item) {
                /** @var \App\Entity\Product $product */
                $product = $em->getRepository(Product::class)->findOneBy(['id' => $item['id']]);
                $productList[$key] = $product->toArray();

                $productList[$key]['name'] = $product->getName($userLanguage);
                $productList[$key]['description'] = $product->getDescription($userLanguage);
                $productList[$key]['price'] = $product->getPrice($userCurrency);
                $productList[$key]['in_sale'] = $product->isInSale($userCurrency);
                $productList[$key]['price_sale'] = $productList[$key]['in_sale'] ? $product->getSalePrice($userCurrency) : false;
            }
            $ordersData[$index] = $productList;
            // TODO: Format delivery type
        }

        return $this->render('theme_admin1/orders.html.twig', [
            'navigation_links' => $navigation,
            'current_store' => $store,
            'orders' => $orders,
            'orders_data' => $ordersData,
            'language' => $userLanguage,
            'currency' => $userCurrency,
        ]);
    }

    public function vouchers(ObjectManager $em, $navigation, $store)
    {
        /** @var \App\Entity\Voucher[] $vouchers */
        $vouchers = $em->getRepository(Voucher::class)->findBy(['store' => $store]);

        return $this->render('theme_admin1/vouchers.html.twig', [
            'navigation_links' => $navigation,
            'current_store' => $store,
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $em
     * @param \Symfony\Component\HttpFoundation\Request  $request
     * @param                                            $navigation
     * @param \App\Entity\Store                          $store
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createVoucher(ObjectManager $em, Request $request, $navigation, $store)
    {
        $addVoucherForm = $this->createForm(AddVoucherType::class);
        $addVoucherForm->handleRequest($request);
        if ($addVoucherForm->isSubmitted() && $addVoucherForm->isValid()) {
            $formData = $addVoucherForm->getData();

            $voucher = (new Voucher())
                ->setStore($store)
                ->setCode($formData['code'])
                ->setType($formData['type'])
                ->setAmount($formData['amount']);
            $em->persist($voucher);
            $em->flush();

            header('Location: '.$this->generateUrl('store_admin', ['store' => $store->getUrl(), 'page' => 'vouchers']));
            exit;
        }

        return $this->render('theme_admin1/create_voucher.html.twig', [
            'navigation_links' => $navigation,
            'current_store' => $store,
            'add_voucher_form' => $addVoucherForm->createView(),
        ]);
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $em
     * @param \Symfony\Component\HttpFoundation\Request  $request
     * @param \App\Entity\Store                          $store
     */
    public function changeOrderStatusTo1(ObjectManager $em, Request $request, $store)
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($user instanceof UserInterface && $store->getOwner()->getId() === $user->getId()) {
            /** @var \App\Entity\Order $update */
            $update = $em->getRepository(Order::class)->findOneBy(['id' => $request->query->get('order_id')]);
            $update->setStatus(Order::STATUS_IN_PRODUCTION);
            $em->flush();
        }

        header('Location: '.$this->generateUrl('store_admin', ['store' => $store->getUrl(), 'page' => 'orders']));
        exit;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $em
     * @param \Symfony\Component\HttpFoundation\Request  $request
     * @param \App\Entity\Store                          $store
     */
    public function changeOrderStatusTo2(ObjectManager $em, Request $request, $store)
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($user instanceof UserInterface && $store->getOwner()->getId() === $user->getId()) {
            /** @var \App\Entity\Order $update */
            $update = $em->getRepository(Order::class)->findOneBy(['id' => $request->query->get('order_id')]);
            $update->setStatus(Order::STATUS_SENT);
            $em->flush();
        }

        header('Location: '.$this->generateUrl('store_admin', ['store' => $store->getUrl(), 'page' => 'orders']));
        exit;
    }
}
