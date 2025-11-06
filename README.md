# ForBill WhatsApp VTU Bot

🎉 **ForBill** is an AI-driven WhatsApp bot for instant VTU (Virtual Top-Up) services including airtime, data bundles, electricity bills, and TV subscriptions.

## 🚀 Features

### 📱 VTU Services
- **Airtime Top-up**: All Nigerian networks (MTN, Airtel, GLO, 9Mobile)
- **Data Bundles**: Multiple plans for all networks
- **Electricity Bills**: Coming soon
- **TV Subscriptions**: Coming soon

### 🤖 WhatsApp Bot Features
- **Natural Language Processing**: Rule-based intent recognition
- **Multi-step Conversations**: Guided transaction flows
- **Wallet System**: User balance management
- **Transaction Tracking**: Complete audit trail
- **Real-time Notifications**: Success/failure messages

### 🏗️ Technical Stack
- **Backend**: Laravel 11.x
- **Database**: SQLite (production ready for PostgreSQL/MySQL)
- **WhatsApp API**: Meta WhatsApp Cloud API
- **Payment Gateway**: Paystack/Flutterwave ready
- **VTU Integration**: Multiple provider support

## 📋 Prerequisites

- PHP 8.3+
- Composer
- Laravel 11.x
- WhatsApp Business API access
- Meta Developer Account

## 🔧 Installation

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/forbill-whatsapp-bot.git
cd forbill-whatsapp-bot
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Environment Variables
```env
# WhatsApp Cloud API Configuration
WHATSAPP_TOKEN=your_access_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_BUSINESS_ACCOUNT_ID=your_business_account_id
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_verify_token
WHATSAPP_API_VERSION=v22.0

# Payment Gateway (Optional)
PAYSTACK_PUBLIC_KEY=pk_test_your_public_key
PAYSTACK_SECRET_KEY=sk_test_your_secret_key

# VTU Provider (Configure with your provider)
VTU_PROVIDER_BASE_URL=https://api.vtuprovider.com
VTU_PROVIDER_API_KEY=your_vtu_api_key
VTU_PROVIDER_SECRET_KEY=your_vtu_secret
```

### 5. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 6. Start Development Server
```bash
php artisan serve
```

## 🌐 WhatsApp Setup

### 1. Configure Webhook in Meta Developer Console
- **Webhook URL**: `https://yourdomain.com/webhook/whatsapp`
- **Verify Token**: Use the token from your `.env` file
- **Subscribe to**: messages, message_deliveries

### 2. Test the Bot
Send these messages to your WhatsApp Business number:
- `hello` - Welcome message
- `help` - Command menu
- `balance` - Check wallet balance
- `airtime` - Airtime purchase instructions
- `data` - Data bundle instructions

## 🚀 Deployment

### Railway Deployment
1. Push to GitHub
2. Connect Railway to your repository
3. Set environment variables
4. Deploy automatically

### Environment Variables for Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-railway-app.railway.app

DB_CONNECTION=postgresql
DB_HOST=your_db_host
DB_PORT=5432
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

## 📱 Bot Commands

| Command | Description |
|---------|-------------|
| `hello`, `hi`, `start` | Welcome message with service overview |
| `help`, `menu` | Display available commands |
| `balance`, `wallet` | Check wallet balance |
| `airtime` | Instructions for buying airtime |
| `data` | Instructions for data bundles |
| `status` | Check transaction status |

## 💰 Transaction Flow

1. **User Request**: "Buy ₦500 MTN airtime for 08012345678"
2. **Bot Confirmation**: Confirms amount, network, and phone number
3. **Payment**: Wallet deduction or payment link
4. **VTU Purchase**: API call to provider
5. **Notification**: Success/failure message with receipt

## 🔧 API Endpoints

### Webhook Endpoints
- `GET /webhook/whatsapp` - Webhook verification
- `POST /webhook/whatsapp` - Receive messages
- `POST /test/whatsapp` - Test message sending

### Admin Endpoints (Coming Soon)
- Dashboard for transaction monitoring
- User management
- Provider configuration

## 🗄️ Database Schema

### Main Tables
- `users` - WhatsApp users with wallet balances
- `transactions` - Complete transaction records
- `vtu_providers` - Network provider configurations
- `payments` - Payment gateway transactions
- `conversation_sessions` - WhatsApp conversation states

## 🧪 Testing

### Run Tests
```bash
php artisan test
```

### Test Bot Locally
```bash
./test_bot.sh
```

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Contact: support@forbill.com

## 🔮 Roadmap

- [ ] Advanced AI integration with OpenAI
- [ ] Electricity bill payments
- [ ] TV subscription services
- [ ] Multi-language support
- [ ] Admin dashboard
- [ ] Analytics and reporting
- [ ] Telegram bot integration

---

**Built with ❤️ for seamless VTU services in Nigeria**

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
