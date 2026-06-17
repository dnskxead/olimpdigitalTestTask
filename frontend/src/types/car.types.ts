// src/types/car.types.ts
export interface CarAvailability {
    id: number;
    name: string;
    year: number;
    color: string | null;
    brand: string | null;
    number: string | null;
    body_type: string | null;
    create: string | null;
    car_types: string;
    free: number;
    service: number;
    busy: number;
    all: number;
}

export interface FilterParams {
    year: number;
    month: number;
}
