# 🛍️ E-Shop Gallery

A full-stack e-commerce application built with Symfony6.4 and Vue.js, featuring product filtering, cart management with discounts.

## 🌟 Features

### Core Functionality
- **Product Catalog** - Browse products fetched from Fake Store API
- **Cart System** - Cart management with 10% discount on orders over €200
- **Search** - Instant product search with debounced input
- **Filtering** - Filter by category, price range, and sort options
- **Session Persistence** - Cart maintains state across browser sessions

### Technical Highlights
- **Responsive Design** - Mobile-first approach with Tailwind CSS v4
- **Type Safety** - Full TypeScript implementation
- **Error Handling** - Comprehensive error management and user feedback
- **Performance Optimized** - Debounced searches and efficient API calls

## 🏗️ Architecture

### Backend (Symfony 6.4)
```
src/
├── Controller/          # API endpoints with validation
├── Service/            # Business logic and external API integration
├── DTO/               # Data transfer objects and response formatting
└── Entity/            # Data models (future expansion)
```

### Frontend (Vue 3 + TypeScript)
```
src/
├── components/        # Reusable UI components
├── composables/      # Vue composables for state management
├── services/         # API client and utilities
├── types/           # TypeScript type definitions
└── views/           # Page components
```

## 🚀 Quick Start

### Prerequisites
- Docker and Docker Compose
- Make (optional, for convenience commands)

### Installation

1. **Clone the repository**
```bash
git clone https://gitlab.com/fr_kata_sf/2025-06-30-mini-ecomm-atou.git eshop
cd eshop
```

2. **Start the application**
```bash
make up
```
*Or manually:*
```bash
docker-compose up -d --build
```

3. **Install dependencies**
```bash
make install
```

4. **Access the application**
- **Frontend**: http://localhost:8080
- **API Documentation**: http://localhost:8080/api
- **Direct Frontend**: http://localhost:3000 (development only)

## 📋 Available Commands

| Command | Description |
|---------|-------------|
| `make up` | Start all services |
| `make down` | Stop all services |
| `make install` | Install backend and frontend dependencies |
| `make logs` | View application logs |
| `make bash` | Access backend container |
| `make frontend-bash` | Access frontend container |
| `make clean` | Remove all containers and volumes |

## 🔌 API Endpoints

### Products
```http
GET /api/products                    # List all products
GET /api/products?limit=10          # Paginated results
GET /api/products?sort=desc         # Sort by price
GET /api/products?category=jewelery # Filter by category
GET /api/products?search=shirt      # Search products
GET /api/products/{id}              # Get single product
GET /api/products/categories        # List categories
```

### Cart Management
```http
GET /api/cart                       # Get current cart
POST /api/cart/add                  # Add item to cart
PUT /api/cart/item/{id}            # Update item quantity
DELETE /api/cart/item/{id}         # Remove item
DELETE /api/cart/clear             # Clear entire cart
GET /api/cart/count                # Get cart item count
```

### Example API Response
```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": {
    "products": [
      {
        "id": 1,
        "title": "Fjallraven - Foldsack No. 1 Backpack",
        "price": 109.95,
        "currency": "EUR",
        "description": "Your perfect pack for everyday use...",
        "category": "men's clothing",
        "image": "https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_.jpg",
        "rating": {
          "rate": 3.9,
          "count": 120
        }
      }
    ],
    "meta": {
      "total": 20,
      "limit": null,
      "filters": {
        "category": null,
        "min_price": null,
        "max_price": null
      }
    }
  }
}
```

## 🛠️ Technology Stack

### Backend
- **PHP 8.4** - Modern PHP with strong typing
- **Symfony 6.4** - Robust framework with API Platform
- **Nelmio CORS** - Cross-origin resource sharing
- **Docker** - Containerized development environment

### Frontend
- **Vue 3** - Progressive JavaScript framework with Composition API
- **TypeScript** - Type-safe JavaScript development
- **Tailwind CSS v4** - Utility-first CSS framework
- **Vite** - Fast build tool and development server
- **Pinia** - State management (prepared for complex state)

### Infrastructure
- **Docker Compose** - Multi-container application orchestration
- **Nginx** - Web server and reverse proxy
- **PHP-FPM** - FastCGI Process Manager

## 🎯 Business Logic

### Discount System
The application implements a tiered discount system:
- **Threshold**: €200.00
- **Discount Rate**: 10%
- **Application**: Automatic when cart total exceeds threshold
- **Display**: Real-time progress indicator

## 🔧 Configuration

### Environment Variables
```bash
# Backend Configuration
APP_ENV=dev
APP_SECRET=your-secret-key
FAKE_STORE_API_URL=https://fakestoreapi.com
CART_DISCOUNT_THRESHOLD=200.00
CART_DISCOUNT_RATE=0.10

# Frontend Configuration
VITE_API_BASE_URL=http://localhost:8080/api
VITE_APP_TITLE=E-Shop Gallery
```

### Docker Configuration
The application uses Docker Compose for consistent development environments:
- **PHP-FPM** container for backend processing
- **Nginx** container for web server and routing
- **Node.js** container for frontend development
- **Shared network** for inter-service communication

## 📈 Performance Considerations

### Frontend Optimizations
- **Debounced Search** - 500ms delay to reduce API calls
- **Price Filter Debouncing** - 800ms delay for smooth slider interaction
- **Lazy Loading** - Images load as needed

### Backend Optimizations
- **Session-based Cart** - Fast cart operations
- **Filtering** - Backend processing before data transfer

## 🧪 Testing

### Manual Testing Checklist
- [ ] Product listing loads correctly
- [ ] Search functionality works with debouncing
- [ ] Category filtering returns accurate results
- [ ] Price range filtering functions properly
- [ ] Cart operations (add, update, remove) work
- [ ] Discount calculation activates at €200
- [ ] Responsive design works on mobile devices
- [ ] Error states display appropriately

### API Testing
```bash
# Test product retrieval
curl http://localhost:8080/api/products

# Test cart functionality
curl -X POST http://localhost:8080/api/cart/add \
  -H "Content-Type: application/json" \
  -d '{"productId": 1, "quantity": 2}'
```
