<?php

declare(strict_types=1);

return [
    'order_status' => [
        'pending_payment' => 'Pending Payment',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],

    'payment_status' => [
        'pending' => 'Pending Payment',
        'success' => 'Successful',
        'failed' => 'Failed',
    ],

    'payment_gateway' => [
        'sandbox' => 'Test Sandbox',
        'zarinpal' => 'Zarinpal Payment Gateway',
        'saman' => 'Saman Bank (SEP) Gateway',
        'mellat' => 'Behpardakht Mellat Gateway',
        'snapp_pay' => 'SnappPay BNPL (4 Installments)',
        'card_to_card' => 'Offline Card-to-Card',
        'wallet' => 'Customer Wallet',
    ],

    'payment_gateway_description' => [
        'sandbox' => 'Test payment simulator (no actual charge)',
        'zarinpal' => 'Secure payment with all Shetab bank cards',
        'saman' => 'Direct online gateway via Saman Bank (SEP)',
        'mellat' => 'Direct online gateway via Behpardakht Mellat',
        'snapp_pay' => 'Buy now, pay in 4 interest-free installments',
        'card_to_card' => 'Offline card-to-card transfer with slip submission',
        'wallet' => 'Instant payment using account wallet balance',
    ],

    'order_return_status' => [
        'pending' => 'Pending Review',
        'approved' => 'Approved (Awaiting Return Shipment)',
        'rejected' => 'Rejected',
        'item_received' => 'Item Received in Warehouse',
        'refunded' => 'Refunded to Wallet',
        'cancelled' => 'Cancelled by User',
    ],

    'ticket_department' => [
        'support' => 'Technical & General Support',
        'finance' => 'Finance & Accounting',
        'sales' => 'Sales & Pre-Purchase Inquiry',
        'shipping' => 'Shipping & Tracking',
        'complaints' => 'Complaints & Feedback',
    ],

    'ticket_priority' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ],

    'ticket_status' => [
        'open' => 'Open',
        'answered' => 'Answered',
        'awaiting_reply' => 'Awaiting Customer Reply',
        'closed' => 'Closed',
    ],

    'banner_position' => [
        'home_slider' => 'Home Main Slider',
        'home_middle' => 'Home Middle Wide Banner',
        'home_grid' => 'Home Promotional Grid',
        'sidebar' => 'Catalog Sidebar Banner',
    ],

    'referral_status' => [
        'pending' => 'Pending Friend First Order',
        'completed' => 'Completed (Reward Deposited)',
        'expired' => 'Expired',
    ],

    'wallet_transaction_type' => [
        'deposit' => 'Wallet Deposit / Top-up',
        'withdraw' => 'Order Payment with Wallet',
        'refund' => 'Refund to Wallet',
        'cashback' => 'Loyalty Cashback',
        'admin_adjustment' => 'Admin Manual Adjustment',
    ],

    'coupon_type' => [
        'percentage' => 'Percentage (%)',
        'fixed' => 'Fixed Amount (Rials)',
        'free_shipping' => 'Free Shipping',
    ],

    'coupon_scope' => [
        'all' => 'All Orders',
        'category' => 'Specific Category',
        'product' => 'Specific Products',
    ],

    'review_status' => [
        'pending' => 'Pending Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],

    'shipping_method' => [
        'post_pishtaz' => 'Iran Post Pishtaz',
        'express_courier' => 'City Express Courier',
        'tipax' => 'Tipax Express',
        'freight' => 'Heavy Freight Service',
    ],

    'stock_status' => [
        'in_stock' => 'In Stock',
        'out_of_stock' => 'Out of Stock',
        'pre_order' => 'Pre-order',
    ],

    'attribute_type' => [
        'text' => 'Short Text',
        'select' => 'Single Select',
        'color' => 'Color',
        'boolean' => 'Yes / No',
        'number' => 'Number',
    ],
];
