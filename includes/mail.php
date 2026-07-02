<?php
/**
 * Dakari — Core Email Infrastructure
 * 
 * Provides mail sending capabilities (SMTP via PHPMailer + local fallback)
 * and responsive HTML templates with premium corporate styling.
 */

function send_email(string $to, string $subject, string $html_content, string $alt_content = '', string $reply_to = ''): bool {
    $smtp_enabled = (setting('smtp_enabled', '0') === '1');
    $smtp_host    = setting('smtp_host', '');
    $smtp_port    = (int)setting('smtp_port', '587');
    $smtp_user    = setting('smtp_user', '');
    $smtp_pass    = setting('smtp_pass', '');
    $smtp_secure  = setting('smtp_secure', 'tls'); // 'tls', 'ssl', 'none'
    $from_email   = setting('mail_from_email', setting('site_email', 'info@dakari.com'));
    $from_name    = setting('mail_from_name', setting('site_name', 'Dakari'));

    if ($smtp_enabled && $smtp_host && $smtp_user && $smtp_pass) {
        require_once __DIR__ . '/PHPMailer/Exception.php';
        require_once __DIR__ . '/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp_pass;
            $mail->Port       = $smtp_port;
            $mail->CharSet    = 'UTF-8';

            if ($smtp_secure === 'ssl') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtp_secure === 'tls') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            // Set From and To
            $mail->setFrom($from_email, $from_name);
            $mail->addAddress($to);
            if ($reply_to) $mail->addReplyTo($reply_to);
            $mail->isHTML(true);

            $mail->Subject = $subject;
            $mail->Body    = $html_content;
            $mail->AltBody = $alt_content ? $alt_content : strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $html_content));

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer SMTP Error sending to $to: " . $mail->ErrorInfo . " | Exception: " . $e->getMessage());
            // Fall through to local mail() on SMTP error
        }
    }

    // Fallback: Native PHP mail() with proper headers
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . mb_encode_mimeheader($from_name) . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: <" . ($reply_to ?: $from_email) . ">\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    $result = mail($to, $subject, $html_content, $headers);
    if (!$result) {
        error_log("Local PHP mail() failed to send email to $to.");
    }
    return $result;
}

/**
 * Standard Dakari responsive HTML layout wrapper
 */
function email_layout(string $title, string $body_content): string {
    $site_name = setting('site_name', 'Dakari');
    $accent = '#C9A84C';
    $primary = '#1B4332';
    $site_url = BASE_URL;

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f6f9f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse; width: 100%; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f6f9f6; padding-bottom: 40px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background-color: {$primary}; padding: 32px 24px; text-align: center; border-bottom: 4px solid {$accent}; }
        .logo { font-size: 28px; font-weight: bold; letter-spacing: 1px; color: #ffffff; font-family: 'Georgia', serif; text-decoration: none; }
        .body { padding: 40px 32px; color: #2D3748; line-height: 1.6; }
        .footer { background-color: #f1f5f1; padding: 24px; text-align: center; color: #718096; font-size: 12px; border-top: 1px solid #e2e8f0; }
        .footer a { color: {$primary}; text-decoration: none; font-weight: bold; }
        .btn { display: inline-block; padding: 12px 28px; background-color: {$primary}; color: #ffffff !important; font-weight: bold; text-decoration: none; border-radius: 4px; margin: 20px 0; border: 1px solid {$primary}; }
        .btn:hover { background-color: #143527; }
        .divider { border-top: 1px solid #e2e8f0; margin: 24px 0; }
    </style>
</head>
<body>
    <table class="wrapper" width="100%">
        <tr>
            <td align="center">
                <table class="container" width="600">
                    <!-- Header -->
                    <tr>
                        <td class="header">
                            <a href="{$site_url}" class="logo">{$site_name}</a>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td class="body">
                            {$body_content}
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            <p style="margin: 0 0 8px 0;">&copy; {date('Y')} {$site_name}. All rights reserved.</p>
                            <p style="margin: 0 0 16px 0;">Nairobi, Kenya | <a href="{$site_url}">Visit Store</a></p>
                            <p style="margin: 0; font-size: 11px; color: #a0aec0;">You received this email because you made a purchase or account action on our platform.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * HTML Template for Storefront Order Invoice
 */
function email_template_order_invoice(array $order, array $items): string {
    $primary = '#1B4332';
    $accent = '#C9A84C';
    $order_num = htmlspecialchars($order['order_number']);
    $currency = setting('currency_symbol', 'KSh');
    
    // Build order items rows
    $items_html = '';
    foreach ($items as $item) {
        $price = money($item['price']);
        $total = money($item['price'] * $item['quantity']);
        $name  = htmlspecialchars($item['product_name']);
        $qty   = (int)$item['quantity'];
        
        $items_html .= "
        <tr>
            <td style='padding: 12px 0; border-bottom: 1px solid #edf2f7;'>
                <strong style='font-size: 14px; color: #2d3748;'>{$name}</strong>
                <div style='font-size: 12px; color: #718096;'>Qty: {$qty} &times; {$price}</div>
            </td>
            <td align='right' style='padding: 12px 0; border-bottom: 1px solid #edf2f7; font-weight: bold; color: #2d3748;'>
                {$total}
            </td>
        </tr>";
    }

    $subtotal = money((float)$order['subtotal']);
    $shipping = $order['shipping_cost'] == 0 ? 'Free' : money((float)$order['shipping_cost']);
    $discount = $order['discount'] > 0 ? '-' . money((float)$order['discount']) : '';
    $tax      = $order['tax'] > 0 ? money((float)$order['tax']) : '';
    $total    = money((float)$order['total']);
    
    $shipping_lines = '';
    if ($order['shipping_cost'] > 0) {
        $shipping_lines .= "
        <tr>
            <td style='padding: 6px 0; color: #718096;'>Shipping</td>
            <td align='right' style='padding: 6px 0; font-weight: bold;'>{$shipping}</td>
        </tr>";
    } else {
        $shipping_lines .= "
        <tr>
            <td style='padding: 6px 0; color: #718096;'>Shipping</td>
            <td align='right' style='padding: 6px 0; font-weight: bold; color: #48bb78;'>Free</td>
        </tr>";
    }
    
    if ($order['discount'] > 0) {
        $coupon_code = htmlspecialchars($order['coupon_code'] ?? 'Discount');
        $shipping_lines .= "
        <tr>
            <td style='padding: 6px 0; color: #e53e3e;'>Discount ({$coupon_code})</td>
            <td align='right' style='padding: 6px 0; font-weight: bold; color: #e53e3e;'>{$discount}</td>
        </tr>";
    }
    
    if ($order['tax'] > 0) {
        $shipping_lines .= "
        <tr>
            <td style='padding: 6px 0; color: #718096;'>Tax</td>
            <td align='right' style='padding: 6px 0; font-weight: bold;'>{$tax}</td>
        </tr>";
    }

    $payment_method = strtoupper($order['payment_method']);
    if ($payment_method === 'COD') $payment_method = 'Cash on Delivery';
    
    $customer_name = htmlspecialchars($order['ship_name']);
    $address = htmlspecialchars($order['ship_address'] . ', ' . $order['ship_city']);
    if ($order['ship_state']) $address .= ', ' . htmlspecialchars($order['ship_state']);
    
    $order_url = BASE_URL . '/client/orders.php';
    
    $body = <<<HTML
    <h2 style="font-family: 'Georgia', serif; font-size: 22px; color: {$primary}; margin-top: 0;">Thank you for your order!</h2>
    <p>Dear {$customer_name},</p>
    <p>We are delighted to confirm that your order <strong>#{$order_num}</strong> has been received and is currently being processed. Below is a summary of your order details:</p>
    
    <div style="background-color: #f7fafc; padding: 20px; border-radius: 6px; margin: 24px 0;">
        <table style="width: 100%; font-size: 14px;">
            <tr>
                <td style="padding-bottom: 8px; color: #718096;">Order Number:</td>
                <td style="padding-bottom: 8px; font-weight: bold;" align="right">#{$order_num}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #718096;">Date:</td>
                <td style="padding-bottom: 8px; font-weight: bold;" align="right">{$order['created_at']}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; color: #718096;">Payment Method:</td>
                <td style="padding-bottom: 8px; font-weight: bold;" align="right">{$payment_method}</td>
            </tr>
            <tr>
                <td style="color: #718096;">Delivery Address:</td>
                <td style="font-weight: bold;" align="right">{$address}</td>
            </tr>
        </table>
    </div>

    <h3 style="font-size: 16px; color: {$primary}; border-bottom: 2px solid #edf2f7; padding-bottom: 8px; margin-top: 32px; margin-bottom: 12px;">Items Ordered</h3>
    <table width="100%">
        {$items_html}
        <!-- Totals lines -->
        <tr>
            <td style="padding: 16px 0 6px 0; color: #718096;">Subtotal</td>
            <td align="right" style="padding: 16px 0 6px 0; font-weight: bold;">{$subtotal}</td>
        </tr>
        {$shipping_lines}
        <tr>
            <td style="padding: 12px 0; border-top: 2px solid #edf2f7; font-size: 16px; font-weight: bold; color: {$primary};">Total</td>
            <td align="right" style="padding: 12px 0; border-top: 2px solid #edf2f7; font-size: 18px; font-weight: bold; color: {$primary};">{$total}</td>
        </tr>
    </table>

    <div style="text-align: center; margin: 36px 0 12px 0;">
        <a href="{$order_url}" class="btn" style="color: #ffffff;">View Order Status</a>
    </div>

    <p style="font-size: 13px; color: #718096; margin-top: 30px;">If you have any questions or concerns regarding this order, please do not hesitate to contact our support team at <a href="mailto:info@dakari.com" style="color: {$primary};">info@dakari.com</a>.</p>
HTML;

    return email_layout("Order Confirmation #{$order_num}", $body);
}

/**
 * HTML Template for Order Status Change
 */
function email_template_status_update(array $order): string {
    $primary = '#1B4332';
    $order_num = htmlspecialchars($order['order_number']);
    $status = htmlspecialchars($order['status']);
    
    // Status visual map
    $status_color = '#C9A84C';
    $status_desc = '';
    
    switch ($status) {
        case 'processing':
            $status_color = '#3b82f6';
            $status_desc = 'We are now compiling and wrapping your products. We will notify you as soon as they are shipped.';
            break;
        case 'shipped':
            $status_color = '#f59e0b';
            $status_desc = 'Your package is on its way to your destination! Our carrier will get in touch with you shortly.';
            break;
        case 'delivered':
            $status_color = '#10b981';
            $status_desc = 'Your package has been successfully delivered. We hope you enjoy your premium products!';
            break;
        case 'cancelled':
            $status_color = '#ef4444';
            $status_desc = 'Your order has been cancelled. If this is a mistake or you require a refund, please contact support.';
            break;
        case 'refunded':
            $status_color = '#8b5cf6';
            $status_desc = 'Your order has been refunded. The funds should show in your account in accordance with your payment provider guidelines.';
            break;
        default:
            $status_desc = 'The status of your order has been updated.';
            break;
    }
    
    $customer_name = htmlspecialchars($order['ship_name']);
    $order_url = BASE_URL . '/client/orders.php';
    
    $body = <<<HTML
    <h2 style="font-family: 'Georgia', serif; font-size: 22px; color: {$primary}; margin-top: 0;">Order Status Update</h2>
    <p>Dear {$customer_name},</p>
    <p>The status of your order <strong>#{$order_num}</strong> has been updated to:</p>
    
    <div style="text-align: center; margin: 30px 0; padding: 20px; background-color: #f7fafc; border-radius: 6px;">
        <span style="font-size: 18px; font-weight: bold; text-transform: uppercase; color: {$status_color}; background-color: #ffffff; border: 1.5px solid {$status_color}; padding: 8px 24px; border-radius: 4px; display: inline-block;">
            {$status}
        </span>
        <p style="margin: 16px 0 0 0; font-size: 14px; color: #4a5568; line-height: 1.5;">
            {$status_desc}
        </p>
    </div>

    <div style="text-align: center; margin: 30px 0 10px 0;">
        <a href="{$order_url}" class="btn" style="color: #ffffff;">View Order in Dashboard</a>
    </div>

    <p style="font-size: 13px; color: #718096; margin-top: 30px;">If you have any questions, feel free to reply to this email or write to us at <a href="mailto:info@dakari.com" style="color: {$primary};">info@dakari.com</a>.</p>
HTML;

    return email_layout("Order #{$order_num} Status Update: " . ucfirst($status), $body);
}
