<?php

declare(strict_types=1);

return [
    'returns' => [
        'only_delivered_orders' => 'Return request can only be submitted for delivered orders.',
        'window_expired' => 'The 7-day return period for this order has expired.',
        'already_submitted' => 'An active return request already exists for this order.',
        'submitted_success' => 'Your return request has been submitted successfully and will be reviewed by support.',
        'refund_wallet_description' => 'Refund for returned order :order_number to wallet',
        'item_received' => 'Returned item received in warehouse.',
        'rejected' => 'Return request rejected.',
        'approved' => 'Return request approved.',
    ],

    'questions' => [
        'submitted_success' => 'Your question has been submitted successfully and will be displayed after review.',
        'answer_submitted_success' => 'Your answer has been submitted and will be published after review.',
        'liked_success' => 'Answer liked successfully.',
        'unliked_success' => 'Like removed.',
        'staff_answer_published' => 'Official answer published successfully.',
    ],

    'referral' => [
        'invalid_code' => 'The entered referral code is invalid.',
        'self_referral' => 'You cannot use your own referral code.',
        'already_referred' => 'You have already been referred by another user.',
        'claimed_success' => 'Referral code claimed successfully. Reward will be settled upon first completed order.',
        'reward_description' => 'Referral reward for inviting :user (Order :order)',
    ],

    'tickets' => [
        'created_success' => 'Support ticket created successfully and will be answered shortly.',
        'already_closed' => 'This ticket is closed and cannot receive new messages.',
        'reply_success' => 'Your reply has been sent successfully.',
        'closed_success' => 'Ticket closed successfully.',
        'staff_reply_success' => 'Reply posted successfully.',
    ],

    'wallet' => [
        'topup_success' => 'Your wallet has been topped up successfully.',
        'redirecting_gateway' => 'Redirecting to payment gateway...',
        'min_deposit' => 'Deposit amount must be greater than zero.',
        'insufficient_balance' => 'Insufficient wallet balance.',
        'order_deduction' => 'Wallet payment deduction for order :order_number',
    ],

    'cart' => [
        'abandoned_reminder_sms' => 'Hello :name, your shopping cart is waiting for you at Reyhan! Complete your purchase: :url',
    ],

    'shipping' => [
        'tracking_sms' => 'Your order :order_number has been shipped. Tracking code: :tracking_code Track online: :tracking_url',
    ],

    'orders' => [
        'paid_sms' => 'Your order :order_number with tracking code :tracking_code has been paid successfully. Thank you for shopping with us.',
        'shipped_sms' => "Dear customer, your order :order_number has been shipped.\nTracking Code: :tracking_code:tracking_url",
    ],

    'catalog' => [
        'stock_alert_sms' => 'Dear customer, the item ":product" (:variant) is back in stock. Visit our shop to purchase.',
    ],
];
