// src/types/car.types.ts
export interface CarAvailability {
    id: number;
    name: string;
    year: number;
    number: string | null;
    body_type?: string;
    free: number;
    busy: number;
    all: number;
    service?: number;
}

export interface FilterParams {
    year: number;
    month: number;
}