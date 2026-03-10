# Connect Frontend + Backend (Local Placeholder)

## Assumptions
- Backend: http://localhost:8000
- Frontend: http://localhost:5173
- API base: http://localhost:8000/api

## Backend
1) Copy env:
   cp .env.example .env
2) Install deps:
   composer install
3) Key + migrate:
   php artisan key:generate
   php artisan migrate
4) Run:
   php artisan serve --host=0.0.0.0 --port=8000

CORS is preconfigured to allow:
- http://localhost:5173
- http://localhost:3000
and headers: Authorization, X-Salon-Id

## Frontend
1) Install deps:
   npm install
2) Ensure .env.local is present (already included):
   VITE_API_BASE_URL=http://localhost:8000/api
3) Run:
   npm run dev -- --host 0.0.0.0 --port 5173

## Tenancy
Frontend sends:
- Authorization: Bearer <token>
- X-Salon-Id: <active salon id>

Backend must enforce tenant scoping by X-Salon-Id + user membership.

## Quick smoke test
- Login
- Select salon
- Open Services page -> load list
- Open Staff page -> load list
- Create appointment -> expect 201
- Create conflict -> expect 409
