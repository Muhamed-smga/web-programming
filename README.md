# web-programming
# Sarajevo2B

Sarajevo2B is a responsive single-page web application designed for locals and tourists to discover coffee shops, sweet shops, clubs, and events in Sarajevo. Users can explore places, view details, check upcoming events, leave reviews, and save their favorite locations. It uses HTML, CSS, JavaScript, jQuery, Bootstrap, a PHP REST API, and MySQL.

## Entities

The project is built around these entities:

- `users` - registered application users and their roles
- `places` - coffee shops, sweet shops, clubs, and other locations in Sarajevo
- `categories` - categories used to organize places by type
- `events` - upcoming concerts, parties, festivals, and other events
- `reviews` - user ratings and feedback for places
- `favorites` - places saved by users for quick access
- `place_images` - images associated with listed places

## Roles

- **Admin** - manages users, places, categories, events, reviews, images, and other administrative settings.
- **User** - browses places and events, searches and filters locations, views place details, writes reviews, and manages their own favorite places.
Running the project
Option 1: XAMPP
Place the project in the XAMPP htdocs directory.
Start Apache and MySQL from the XAMPP Control Panel.
Open http://localhost/web-project1/frontend/ in a browser.
Option 2: PHP built-in server
From the project root, run:

php -S localhost:8000 server.php
Then open http://localhost:8000/frontend/ in a browser. The server.php development router serves the frontend and forwards backend requests to the PHP API.