<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Reyhan\Core\Models\AbandonedCartLog;
use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\Admin;
use Reyhan\Core\Models\Attribute;
use Reyhan\Core\Models\AttributeValue;
use Reyhan\Core\Models\Banner;
use Reyhan\Core\Models\BlogCategory;
use Reyhan\Core\Models\BlogPost;
use Reyhan\Core\Models\Brand;
use Reyhan\Core\Models\CardTransferReceipt;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Models\CartItem;
use Reyhan\Core\Models\Category;
use Reyhan\Core\Models\City;
use Reyhan\Core\Models\ContactMessage;
use Reyhan\Core\Models\Coupon;
use Reyhan\Core\Models\CouponUsage;
use Reyhan\Core\Models\Faq;
use Reyhan\Core\Models\LoyaltyTransaction;
use Reyhan\Core\Models\Order;
use Reyhan\Core\Models\OrderItem;
use Reyhan\Core\Models\OrderReturn;
use Reyhan\Core\Models\OrderReturnItem;
use Reyhan\Core\Models\Page;
use Reyhan\Core\Models\Payment;
use Reyhan\Core\Models\Product;
use Reyhan\Core\Models\ProductAnswer;
use Reyhan\Core\Models\ProductPriceHistory;
use Reyhan\Core\Models\ProductQuestion;
use Reyhan\Core\Models\ProductSpecification;
use Reyhan\Core\Models\ProductVariant;
use Reyhan\Core\Models\ProductVariantValue;
use Reyhan\Core\Models\Province;
use Reyhan\Core\Models\Referral;
use Reyhan\Core\Models\Review;
use Reyhan\Core\Models\ShippingMethod;
use Reyhan\Core\Models\Specification;
use Reyhan\Core\Models\SpecificationGroup;
use Reyhan\Core\Models\StockAlert;
use Reyhan\Core\Models\SupportTicket;
use Reyhan\Core\Models\SupportTicketMessage;
use Reyhan\Core\Models\User;
use Reyhan\Core\Models\WalletTransaction;
use Reyhan\Core\Models\Wishlist;

test('every domain model has a working factory and can be instantiated', function (string $modelClass) {
    /** @var Model $model */
    $model = $modelClass::factory()->create();

    expect($model)->toBeInstanceOf($modelClass)
        ->and($model->exists)->toBeTrue();
})->with([
    'AbandonedCartLog' => AbandonedCartLog::class,
    'Address' => Address::class,
    'Admin' => Admin::class,
    'Attribute' => Attribute::class,
    'AttributeValue' => AttributeValue::class,
    'Banner' => Banner::class,
    'BlogCategory' => BlogCategory::class,
    'BlogPost' => BlogPost::class,
    'Brand' => Brand::class,
    'CardTransferReceipt' => CardTransferReceipt::class,
    'Cart' => Cart::class,
    'CartItem' => CartItem::class,
    'Category' => Category::class,
    'City' => City::class,
    'ContactMessage' => ContactMessage::class,
    'Coupon' => Coupon::class,
    'CouponUsage' => CouponUsage::class,
    'Faq' => Faq::class,
    'LoyaltyTransaction' => LoyaltyTransaction::class,
    'Order' => Order::class,
    'OrderItem' => OrderItem::class,
    'OrderReturn' => OrderReturn::class,
    'OrderReturnItem' => OrderReturnItem::class,
    'Page' => Page::class,
    'Payment' => Payment::class,
    'Product' => Product::class,
    'ProductAnswer' => ProductAnswer::class,
    'ProductPriceHistory' => ProductPriceHistory::class,
    'ProductQuestion' => ProductQuestion::class,
    'ProductSpecification' => ProductSpecification::class,
    'ProductVariant' => ProductVariant::class,
    'ProductVariantValue' => ProductVariantValue::class,
    'Province' => Province::class,
    'Referral' => Referral::class,
    'Review' => Review::class,
    'ShippingMethod' => ShippingMethod::class,
    'Specification' => Specification::class,
    'SpecificationGroup' => SpecificationGroup::class,
    'StockAlert' => StockAlert::class,
    'SupportTicket' => SupportTicket::class,
    'SupportTicketMessage' => SupportTicketMessage::class,
    'User' => User::class,
    'WalletTransaction' => WalletTransaction::class,
    'Wishlist' => Wishlist::class,
]);
