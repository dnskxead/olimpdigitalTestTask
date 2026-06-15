import React from 'react';
import { CarProvider } from './context/CarContext';
import { Filters } from './components/Filters';
import { AvailabilityTable } from './components/AvailabilityTable';

const App: React.FC = () => {
    return (
        <CarProvider>
            <div style={{ maxWidth: '1000px', margin: '0 auto', fontFamily: 'sans-serif' }}>
                <h2>Car Availability Dashboard</h2>
                <Filters />
                <AvailabilityTable />
            </div>
        </CarProvider>
    );
};

export default App;