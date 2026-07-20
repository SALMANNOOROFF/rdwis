import './bootstrap';

import React from 'react';
import { createRoot } from 'react-dom/client';
import NrdiCommandDashboard from './nrdi-dashboard/NrdiCommandDashboard';

const rootEl = document.getElementById('nrdi-dashboard-root');
if (rootEl) {
    createRoot(rootEl).render(
        React.createElement(
            React.StrictMode,
            null,
            React.createElement(NrdiCommandDashboard, null)
        )
    );
}
