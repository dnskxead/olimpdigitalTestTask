import React, { useContext, useMemo } from 'react';
import { CarContext } from '../context/CarContext';
export const AvailabilityTable: React.FC = () => {
    const context = useContext(CarContext);
    if (!context) throw new Error('AvailabilityTable must be used within CarProvider');
    const { cars, loading, error } = context;
    const totalFreeDays = useMemo(() => {
        return cars.reduce((sum, car) => sum + car.free, 0);
    }, [cars]);
    if (loading) return <p>Завантаження даних...</p>;
    if (error) return <p style={{ color: 'red' }}>{error}</p>;
    if (cars.length === 0) return <p>Оберіть період і натисніть "Show data"</p>;
    return (
        <div>
            <h3>Results (Total Cars: {cars.length} | Total Free Days: {totalFreeDays})</h3>
            <table border={1} cellPadding={8} style={{ width: '100%', borderCollapse: 'collapse', textAlign: 'center' }}>
                <thead>
                    <tr>
                        <th>id</th>
                        <th>name</th>
                        <th>year</th>
                        <th>number</th>
                        <th style={{ backgroundColor: '#e2f0d9' }}>free</th>
                        <th>busy</th>
                        <th>all</th>
                    </tr>
                </thead>
                <tbody>
                    {cars.map(car => (
                        <tr key={car.id}>
                            <td>{car.id}</td>
                            <td>{car.name}</td>
                            <td>{car.year}</td>
                            <td>{car.number}</td>
                            <td style={{ backgroundColor: '#e2f0d9', fontWeight: 'bold' }}>{car.free}</td>
                            <td>{car.busy}</td>
                            <td>{car.all}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};