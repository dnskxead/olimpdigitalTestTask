import React from 'react';
import { CarProvider } from './context/CarContext';
import { Filters } from './components/Filters';
import { AvailabilityTable } from './components/AvailabilityTable';

const App: React.FC = () => {
    return (
        <CarProvider>
            <main className="app-shell">
                <header className="app-header">
                    <h1>Car Availability Table</h1>
                </header>
                <Filters />
                <AvailabilityTable />
            </main>
        </CarProvider>
    );
};

export default App;
