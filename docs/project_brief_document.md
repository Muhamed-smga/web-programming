# Project Brief: Sarajevo2B (Discover Sarajevo)

## 1. Project Name and Purpose

- Project Name: Sarajevo2B
- Purpose: Sarajevo2B is a responsive single-page web application (SPA) designed to help tourists and local residents discover the best places, attractions, events, and experiences in Sarajevo. The platform brings together popular coffee bars, restaurants, sweet shops, clubs, cultural attractions, and local events in one convenient location. Users can explore places, read community reviews, save their favorite locations, and discover upcoming events, making it easier to experience everything Sarajevo has to offer.

## 2. Target Users

- Tourists & Visitors: People visiting Sarajevo who want to discover popular attractions, restaurants, coffee bars, nightlife, and cultural experiences.
- Local Residents: Sarajevo residents looking for new places to visit, upcoming events, and recommendations from other users.
- Business Owners: Local businesses interested in promoting their establishments and reaching a wider audience.
- Administrators: Platform administrators responsible for managing registered users, places, categories, events, and community reviews.

## 3. Main User Flows

- User Flow:
  1. Authentication: User registers or logs in through a single-page authentication modal/view.
  2. Home Dashboard: User explores the homepage, featuring popular places, newly added locations, upcoming events, and a brief introduction to Sarajevo2B.
  3. Place Discovery: User browses and filters available locations by category, such as coffee bars, restaurants, sweet shops, clubs, bars, and tourist attractions.
  4. Place Details: User opens a specific location to view its description, address, photos, opening hours, contact information, and available reviews.
  5. Favorites Management: User saves interesting places to their personal favorites list and can access them later.
  6. Events Discovery: User browses upcoming events, views event details, dates, locations, and descriptions.
  7. Reviews & Ratings: User submits ratings and written reviews for places they have visited and reads feedback from other community members.
- Admin Flow:
  1. Admin Login: Administrator logs in and receives access to additional administrative functionality based on their JWT user role.
  2. Entity Management (CRUD): Administrator creates, updates, deletes, and manages places, categories, events, reviews, and registered user accounts.
  3. Content Management: Administrator manages featured locations, popular destinations, newly added places, and event information.
  4. Review Moderation: Administrator monitors user reviews and removes inappropriate or misleading content.
  5. System Monitoring: Administrator monitors application logs, user activity, and general platform performance.

## 4. Roles and Permissions

- Admin:
  - Full CRUD access to all five core entities (`users`, `categories`, `places`, `events`, `reviews`).
  - Ability to create, update, and delete places and categories.
  - Ability to manage upcoming events and featured locations.
  - Ability to manage registered user accounts.
  - Ability to moderate community reviews.
  - Access to administrative dashboards and platform analytics.
- User (Client):
  - Read access to publicly available places, categories, and events.
  - Full CRUD access to their own favorites.
  - Ability to create, update, and delete their own reviews.
  - Ability to manage their personal account information.
  - Read access to other users' public reviews.
  - Ability to browse places and events without administrative permissions.
- Guest (Unauthenticated Visitor):
  - Read access to public places and categories.
  - Read access to public events.
  - Read access to published reviews.
  - Access to the homepage and place details.
  - Must register or log in to submit reviews or save favorites.

## 5. Required Pages / Views (SPA Components)

- `views/home.html`: Main homepage featuring popular attractions, recently added places, upcoming events, and an introduction to Sarajevo2B.
- `views/login.html`: Unified authentication form with Login / Registration functionality.
- `views/places.html`: Browse, search, and filter places by category, popularity, and other criteria.
- `views/place-details.html`: Detailed information about an individual place, including photos, location, description, contact information, and reviews.
- `views/categories.html`: Browse available place categories, such as coffee bars, restaurants, sweet shops, clubs, bars, and tourist attractions.
- `views/events.html`: Browse upcoming events and view their dates, locations, descriptions, and details.
- `views/reviews.html`: View community ratings and feedback, submit new reviews, and manage personal reviews.
- `views/favorites.html`: Display and manage the user's saved places.
- `views/admin.html`: Administrative dashboard for managing users, places, categories, events, and reviews.

## 6. Important Entities and Data (Database Schema Concept)

The database will consist of five main relational entities:

1\. `users`

- Fields: `id`, `name`, `email`, `password_hash`, `role` (`admin`/`user`), `created_at`
- Stores registered user information and account permissions.

2\. `categories`

- Fields: `id`, `name`, `description`, `created_at`
- Stores available place categories, such as coffee bars, restaurants, sweet shops, clubs, bars, and tourist attractions.

3\. `places`

- Fields: `id`, `category_id` (FK), `name`, `description`, `address`, `latitude`, `longitude`, `phone`, `website`, `opening_hours`, `image_url`, `created_at`
- Stores information about places and attractions available on the platform.
- Each place belongs to one category.

4\. `events`

- Fields: `id`, `place_id` (FK), `title`, `description`, `event_date`, `start_time`, `end_time`, `image_url`, `created_at`
- Stores information about upcoming events in Sarajevo.
- Each event can be associated with a specific place.

5\. `reviews`

- Fields: `id`, `user_id` (FK), `place_id` (FK), `rating` (1-5), `comment`, `created_at`
- Stores user ratings and written feedback about places.
- Each review belongs to one user and one place.

Additional entity (recommended): `favorites`

- Fields: `id`, `user_id` (FK), `place_id` (FK), `created_at`
- Stores places saved by users.
- Each user can save multiple places, and each place can be saved by multiple users.
- A unique constraint on (`user_id`, `place_id`) prevents duplicate favorites.

## 7. Design Direction

- Framework: Bootstrap 5 with a modern, clean, tourism-oriented design.
- Visual Style: Modern and visually appealing interface featuring Sarajevo-inspired imagery, attractive place cards, clear typography, and a consistent color palette.
- Navigation: Responsive navigation bar containing:
  - Home
  - Categories (dropdown menu)
  - Places
  - Reviews
  - Events
  - Favorites
  - Login / Register
- Category Dropdown: Allows users to quickly filter places by coffee bars, restaurants, sweet shops, clubs, bars, and tourist attractions.
- Usability: Mobile-first responsive design with intuitive navigation and accessible layouts.
- Interactivity: Dynamic SPA view swapping using Vanilla JavaScript and jQuery AJAX without requiring full-page reloads.

## 8. Features in Scope

- Single Page Application (SPA) with modular routing.
- Homepage displaying popular and recently added places.
- Searchable and filterable place directory.
- Category-based place discovery.
- Individual place detail pages.
- Community reviews and rating functionality.
- User authentication and registration.
- Favorites management for registered users.
- Events discovery and management.
- Complete RESTful API built using FlightPHP.
- PDO MySQL database interaction across all core entities using prepared statements.
- Role-Based Access Control (RBAC) using JWT authentication middleware.
- Request logging (`info.log`) and error handling (`error.log`).
- Client-side and server-side form validation.
- Admin dashboard for managing platform content.
- Responsive design for desktop, tablet, and mobile devices.

## 9. Features Intentionally Out of Scope

- Real-time messaging or chat functionality.
- OAuth / Social Login integrations (Google, Facebook).
- Online payment systems or subscription gateways.
- Direct booking and reservation management.
- Business-owner self-service dashboards.
- Live navigation and GPS tracking.
- Advanced user-generated photo and video hosting.
- Real-time event notifications.

## 10. Backend / API Expectations

- Framework: FlightPHP with PHP 8.x.
- Database: MySQL.
- Database Interaction: PDO with prepared statements.
- Architecture: Modular layered architecture following:
Route → Service → DAO → Database
- Authentication: JWT-based authentication with role-based permissions.
- Security: Password hashing, prepared statements, input validation, and protected administrative routes.
- Documentation: OpenAPI / Swagger documentation accessible through a UI endpoint.
- Configuration: All database credentials and secrets stored in an uncommitted `backend/config/config.php`, with `config.example.php` provided as a template.
- Logging: Application information and errors recorded in separate log files.

## 11. Future Plans and Assumptions

- Assumptions:
  - The application will initially run locally using XAMPP.
  - MySQL will be used for relational data storage.
  - Place and event information will initially be managed by administrators.
  - Users can browse public content without registering.
  - Registration is required for submitting reviews and managing favorites.
  - The initial target location is Sarajevo, Bosnia and Herzegovina.
- Future Enhancements:
  - Interactive map displaying places and attractions throughout Sarajevo.
  - Personalized recommendations based on user interests and favorite categories.
  - Advanced filtering based on price, ratings, location, and opening hours.
  - Multilingual support (Bosnian and English).
  - Integration with external event and tourism APIs.
  - Exportable PDF guides for tourists.
  - Business-owner accounts for managing their own establishments.