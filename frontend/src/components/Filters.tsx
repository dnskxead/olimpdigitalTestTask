import React, { useState, useContext, useEffect, useRef } from 'react';
import { CarContext } from '../context/CarContext';

const YEARS = [2024, 2023, 2022, 2021];
const MONTHS = Array.from({ length: 12 }, (_, i) => i + 1);

export const Filters: React.FC = () => {
    const context = useContext(CarContext);
    if (!context) throw new Error('Filters must be used within CarProvider');

    const [year, setYear] = useState<number>(2023);
    const [month, setMonth] = useState<number>(1);
    const selectYearRef = useRef<HTMLSelectElement>(null);
    useEffect(() => {
        selectYearRef.current?.focus();
    }, []);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        context.fetchCars({ year, month });
    };

    return (
        <form className="filters" onSubmit={handleSubmit}>
            <div className="filter-grid">
                <label className="filter-field">
                    <span>Years</span>
                    <select
                    ref={selectYearRef}
                    multiple
                    value={[year.toString()]}
                    onChange={(e) => setYear(Number(e.target.value))}
                >
                    {YEARS.map(y => <option key={y} value={y}>{y}</option>)}
                    </select>
                </label>

                <label className="filter-field">
                    <span>Months</span>
                    <select
                    multiple
                    value={[month.toString()]}
                    onChange={(e) => setMonth(Number(e.target.value))}
                >
                    {MONTHS.map(m => <option key={m} value={m}>{m}</option>)}
                    </select>
                </label>
            </div>

            <button className="primary-button" type="submit">Show data</button>
        </form>
    );
};
