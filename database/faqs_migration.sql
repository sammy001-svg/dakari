-- ============================================================
-- Create FAQs table and seed with default content
-- Run once in cPanel > phpMyAdmin > SQL tab
-- ============================================================

CREATE TABLE IF NOT EXISTS faqs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    category   VARCHAR(100)  NOT NULL,
    question   TEXT          NOT NULL,
    answer     TEXT          NOT NULL,
    sort_order INT           NOT NULL DEFAULT 0,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed with existing hardcoded FAQs
INSERT INTO faqs (category, question, answer, sort_order) VALUES
-- Orders & Shipping
('Orders & Shipping', 'How long does delivery take?', 'Standard delivery within Nairobi takes 1–3 business days. For upcountry Kenya and regional destinations (Uganda, Tanzania, Rwanda), allow 3–7 business days. We dispatch orders placed before 12 pm EAT the same day.', 1),
('Orders & Shipping', 'How much does shipping cost?', 'Shipping is free on orders over KES 3,000. For orders below that threshold, a flat shipping fee of KES 250 applies within Nairobi, and KES 500 for upcountry/regional.', 2),
('Orders & Shipping', 'Can I track my order?', 'Yes. Once your order is dispatched you will receive an SMS and email with your tracking number. You can also log in to your account and view live order status from the "My Orders" section.', 3),
('Orders & Shipping', 'What happens if my order is delayed?', 'If your order has not arrived within the expected window, please contact us via the Contact page or call +254 700 000 000. Our support team will investigate and provide an update within 4 hours.', 4),
('Orders & Shipping', 'Do you offer same-day delivery?', 'Same-day delivery is available within Nairobi CBD and select suburbs for orders placed before 10 am EAT. Select "Same-Day Delivery" at checkout to see if your area qualifies.', 5),
-- Returns & Refunds
('Returns & Refunds', 'What is your return policy?', 'We offer a 14-day hassle-free return policy. If you are not 100% satisfied with your purchase, return the item in its original condition and packaging and we will issue a full refund or exchange.', 1),
('Returns & Refunds', 'How do I initiate a return?', 'Log in to your account, go to "My Orders", select the order and click "Request Return". Fill in the reason and submit. Our team will confirm within 24 hours with return instructions.', 2),
('Returns & Refunds', 'When will I receive my refund?', 'Once we receive and inspect the returned item, refunds are processed within 3–5 business days. M-Pesa refunds reflect within 24 hours; card refunds may take up to 7 business days depending on your bank.', 3),
('Returns & Refunds', 'What items cannot be returned?', 'For hygiene reasons, personal care products, undergarments, and opened consumables cannot be returned unless they arrive damaged or defective. All other items are eligible for return.', 4),
-- Products & Stock
('Products & Stock', 'Are all your products authentic?', 'Absolutely. Dakari only stocks genuine products sourced directly from authorised distributors and brand partners. We do not stock grey-market or counterfeit goods — authenticity is non-negotiable.', 1),
('Products & Stock', 'What does "Out of Stock" mean?', '"Out of Stock" means the item is temporarily unavailable. You can click "Notify Me" on the product page and we will email you the moment stock is replenished.', 2),
('Products & Stock', 'Can I place a bulk or wholesale order?', 'Yes. For orders of 10+ units of a single product, or wholesale enquiries, please fill out the Contact form and select "Partnership / Wholesale" as the category. Our team will send a custom quote within 24 hours.', 3),
('Products & Stock', 'Do your products come with a warranty?', 'Warranty terms vary by product and brand. Warranty information is listed on each product page. For warranty claims, contact the brand directly or reach out to our support team for assistance.', 4),
-- Account & Payments
('Account & Payments', 'How do I create an account?', 'Click "Sign In" in the top navigation and select "Create Account". You will need a valid email address and a password. Account creation is free and takes under 60 seconds.', 1),
('Account & Payments', 'What payment methods do you accept?', 'We accept M-Pesa (Paybill and STK push), Visa, Mastercard, and PayPal. All transactions are secured with 256-bit SSL encryption. We do not store card details.', 2),
('Account & Payments', 'Is my payment information secure?', 'Yes. All payments are processed through PCI-DSS compliant payment gateways. We never store your full card number or CVV. M-Pesa transactions use Safaricom''s secure STK push protocol.', 3),
('Account & Payments', 'Can I checkout as a guest?', 'Yes. You can complete a purchase without creating an account. However, creating an account allows you to track orders, manage returns, save your address, and earn loyalty points.', 4),
('Account & Payments', 'How do I use a discount code?', 'At checkout, enter your discount code in the "Promo Code" field and click "Apply". Valid codes will deduct the discount amount before payment. Codes are case-insensitive.', 5),
-- Support
('Support', 'How do I contact customer support?', 'You can reach us via the Contact page form, by email at hello@dakari.com, or by phone at +254 700 000 000. Our support hours are Monday–Friday 8 am–6 pm EAT and Saturday 9 am–4 pm EAT.', 1),
('Support', 'How long does it take to get a response?', 'We aim to respond to all enquiries within 4 business hours during working hours. Messages received outside business hours are addressed first thing the next morning.', 2),
('Support', 'I forgot my password. What do I do?', 'On the login page, click "Forgot Password?", enter your registered email address, and we will send a secure reset link. The link is valid for 60 minutes.', 3);
