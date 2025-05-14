# Donation System with Stripe Integration

## Overview
This application provides a streamlined donation system with Stripe integration. It features a two-step donation form that allows users to make one-time or monthly donations without storing sensitive payment information in the application's database.

## Features
- Two-step donation form with intuitive UI
- One-time and monthly recurring donation options
- Stripe Checkout integration for secure payment processing
- Optional tip amount calculation
- Anonymous donation option
- Processing fee transparency
- Donation success and cancellation pages
- Webhook handling for Stripe events

## Setup Instructions

### Prerequisites
- PHP 8.2 or higher
- Composer
- Laravel 11.x
- Stripe account

### Installation
1. Clone the repository
   ```
   git clone <repository-url>
   cd donation
   ```

2. Install dependencies
   ```
   composer install
   npm install
   npm run dev
   ```

3. Copy the environment file and configure it
   ```
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure Stripe in your `.env` file
   ```
   STRIPE_KEY=your_stripe_publishable_key
   STRIPE_SECRET=your_stripe_secret_key
   STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret
   CASHIER_CURRENCY=usd
   CASHIER_CURRENCY_LOCALE=en
   ```

5. Set up Stripe webhook
   - In your Stripe dashboard, create a webhook endpoint pointing to: `https://your-domain.com/webhook/stripe`
   - Add the webhook secret to your `.env` file

6. Start the development server
   ```
   php artisan serve
   ```

## Usage
- Visit the homepage to access the donation form
- Complete the two-step process to make a donation
- Payments are processed securely through Stripe

## Assumptions

1. **No Database Storage for Donations**: The system is designed to not store donation data in the application's database. All payment processing and storage is handled by Stripe.

2. **Stripe Account Setup**: It's assumed that you have a Stripe account with API keys and webhook configuration.

3. **Currency**: The default currency is set to USD, but can be changed in the `.env` file.

4. **Payment Method**: The application only supports card payments through Stripe.

5. **Webhook Handling**: The application assumes proper webhook configuration for handling Stripe events.

## License
This project is licensed under the MIT License.
