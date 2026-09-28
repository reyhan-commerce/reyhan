<?php

declare(strict_types=1);

use App\Models\AbandonedCartLog;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Banner;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\CardTransferReceipt;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\City;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Faq;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductAnswer;
use App\Models\ProductPriceHistory;
use App\Models\ProductQuestion;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Models\Province;
use App\Models\Referral;
use App\Models\Review;
use App\Models\ShippingMethod;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use App\Models\StockAlert;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Model;

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
