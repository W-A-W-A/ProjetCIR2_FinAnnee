# ProjetCIR2_FinAnnee
*Group 10 - CIR2 2024-2025*

[Github](https://github.com/W-A-W-A/ProjetCIR2_FinAnnee/commits/main/?after=d0200d82bbca364a2d5a14b33cbfa29a761077f6+69)

# Features

### Homepage Statistics & Navigation
- **Animated Counters**: Displays animated statistics for total installations, regions, installers, brands, and more, fetched directly from the backend.
- **Robust Data Loading**: Uses jQuery AJAX (or fetch API as fallback) to retrieve live stats, with error handling and clear default values if the backend is unreachable.
- **Tab Navigation**: Links to the websites' other pages, making navigation easier.

### search.js — Search & Authentication
- **Authentication Modal**: Restrics access to the website's other features with a modal requiring a secret key (hashed), with optional "remember me" via cookies.
- **Dynamic Search Filters**: Loads filter options (brands, panels, departments) from the backend and updates results live as filters change.
- **Result Display**: Shows search results with key installation stats, each with a button to view their details.
- **Error Handling**: Logs and displays clear errors if backend data cannot be loaded.

### detail.js — Installation Detail & Secure Actions
- **Detail Display**: Loads and displays all details of selected installations, organized into sections (installation, location, parameters).
- **Authentication for Modify/Delete**: Uses a modal to securely allow modification or deletion, with cookie-based session.
- **Modify/Delete Actions**: After authentication, allows users to edit or delete an installation.
- **Error Handling**: Logs and displays clear errors if backend data cannot be loaded.

### create.js — Create or Modify Installation
- **Double Feature (Create/Modify)**: Handles both creation of new installations and modification of existing ones, updating UI and form behavior accordingly.
- **Authentication Check**: Ensures only authenticated users can submit the form to edit data.
- **Form Population**: Pre-fills form fields when modifying an installation.
- **Submission Handling**: Uses Ajax to send form data to the backend, with success/error feedback and redirects.
- **Cancel Button**: Adds a cancel button to return to the previous page.

### map.js — Interactive Map of Installations
- **Map Initialization**: Uses Leaflet to display the map of installations.
- **Dynamic Markers**: Loads installation data (optionally filtered by year/department/region) and displays them.
- **Filter Controls**: Dropdowns for year and department, populated from the backend, update the map in real time.
- **Marker Popups**: Each marker shows a popup with installation details when the user clicks on it.


