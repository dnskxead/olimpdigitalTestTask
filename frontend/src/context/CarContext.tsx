import React, { createContext, useState, useCallback, useRef, ReactNode } from 'react';
import { CarAvailability, FilterParams } from '../types/car.types';
import { carApiService } from '../services/apiService';

interface CarContextProps {
    cars: CarAvailability[];
    loading: boolean;
    error: string | null;
    fetchCars: (params: FilterParams) => Promise<void>;
}
export const CarContext = createContext<CarContextProps | undefined>(undefined);
export const CarProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
    const [cars, setCars] = useState<CarAvailability[]>([]);
    const [loading, setLoading] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);
    const abortControllerRef = useRef<AbortController | null>(null);

    const fetchCars = useCallback(async ({ year, month }: FilterParams) => {
        if (abortControllerRef.current) {
            abortControllerRef.current.abort();
        }
        abortControllerRef.current = new AbortController();

        setLoading(true);
        setError(null);
        try {
            const paddedMonth = String(month).padStart(2, '0');
            const lastDay = new Date(year, month, 0).getDate();
            const startDate = `${year}-${paddedMonth}-01`;
            const endDate = `${year}-${paddedMonth}-${lastDay}`;
            const data = await carApiService.fetchData(startDate, endDate, abortControllerRef.current.signal);
            setCars(data);
        } catch (err: any) {
            if (err.name !== 'AbortError') {
                setError(err.message || 'Невідома помилка');
            }
        } finally {
            setLoading(false);
        }
    }, []);
    return (
        <CarContext.Provider value={{ cars, loading, error, fetchCars }}>
            {children}
        </CarContext.Provider>
    );
};