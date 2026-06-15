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
        <form onSubmit={handleSubmit} style={{ padding: '15px', background: '#f9f9f9', marginBottom: '20px' }}>
            <div style={{ display: 'inline-block', marginRight: '20px' }}>
                <label>Years</label><br />
                <select 
                    ref={selectYearRef}
                    multiple 
                    value={[year.toString()]} 
                    onChange={(e) => setYear(Number(e.target.value))}
                    style={{ width: '100px', height: '100px' }}
                >
                    {YEARS.map(y => <option key={y} value={y}>{y}</option>)}
                </select>
            </div>

            <div style={{ display: 'inline-block' }}>
                <label>Months</label><br />
                <select 
                    multiple 
                    value={[month.toString()]} 
                    onChange={(e) => setMonth(Number(e.target.value))}
                    style={{ width: '100px', height: '100px' }}
                >
                    {MONTHS.map(m => <option key={m} value={m}>{m}</option>)}
                </select>
            </div>
            
            <br />
            <button type="submit" style={{ marginTop: '15px' }}>Show data</button>
        </form>
    );
};