import React, { useContext, useMemo } from 'react';
import { CarContext } from '../context/CarContext';

export const AvailabilityTable: React.FC = () => {
    const context = useContext(CarContext);
    if (!context) throw new Error('AvailabilityTable must be used within CarProvider');

    const { cars, loading, error } = context;
    const totalFreeDays = useMemo(() => {
        return cars.reduce((sum, car) => sum + car.free, 0);
    }, [cars]);

    if (loading) return <p className="state-message">Завантаження даних...</p>;
    if (error) return <p className="state-message state-message-error">{error}</p>;
    if (cars.length === 0) return <p className="state-message">Оберіть період і натисніть "Show data"</p>;

    return (
        <section className="results">
            <div className="results-header">
                <h2>Results</h2>
                <div className="summary">
                    <span>All cars: {cars.length}</span>
                    <span>Total free days: {totalFreeDays}</span>
                </div>
            </div>
            <div className="table-scroll">
                <table className="availability-table">
                    <thead>
                        <tr>
                            <th>id</th>
                            <th>name</th>
                            <th>year</th>
                            <th>color</th>
                            <th>brand</th>
                            <th>number</th>
                            <th>body type</th>
                            <th>create</th>
                            <th>car types</th>
                            <th className="free-column">free</th>
                            <th>service</th>
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
                                <td>{car.color}</td>
                                <td>{car.brand}</td>
                                <td>{car.number}</td>
                                <td>{car.body_type}</td>
                                <td>{car.create}</td>
                                <td>{car.car_types}</td>
                                <td className="free-column">{car.free}</td>
                                <td>{car.service}</td>
                                <td>{car.busy}</td>
                                <td>{car.all}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </section>
    );
};
