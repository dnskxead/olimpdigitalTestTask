import { CarAvailability } from '../types/car.types';

export interface CarApiInterface {
    fetchData(startDate: string, endDate: string, signal?: AbortSignal): Promise<CarAvailability[]>;
}

export const carApiService: CarApiInterface = {
    async fetchData(startDate: string, endDate: string, signal?: AbortSignal): Promise<CarAvailability[]> {
        const response = await fetch(`http://localhost/api/cars/availability?start_date=${startDate}&end_date=${endDate}`, { signal });
        
        if (!response.ok) {
            throw new Error('Помилка завантаження з сервера');
        }
        const data = await response.json();
        return data.data; 
    }
};