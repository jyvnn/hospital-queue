import './bootstrap';

// Vendor CSS imported from node_modules (bundled by Vite)
import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

// Import project CSS via Vite so styles are available in development and production builds
import '../css/base.css';
import '../css/layout.css';
import '../css/navigation.css';
import '../css/cards.css';
import '../css/forms.css';
import '../css/buttons.css';
import '../css/patient-queue.css';
import '../css/doctors.css';
import '../css/patient-history.css';
import '../css/reports.css';
import '../css/loading.css';
import '../css/responsive.css';

// If you have local JS utilities that were previously served from public/assets/js,
// move them into resources/js/ and import them here, for example:
import './ui-animations.js';
// import './api-operations.js';
// import './app-core.js';

// Reports page JS (imports Chart.js via the Vite bundle)
import './reports.js';
import './queue-ajax.js';
