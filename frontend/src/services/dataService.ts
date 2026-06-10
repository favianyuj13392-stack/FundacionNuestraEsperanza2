// frontend/src/services/dataService.ts

import axios from 'axios';
import { API_BASE_URL } from '@/utils/apiBaseUrl';

// Fallback data for when API is unavailable or no data exists
const FALLBACK_PROGRAMS = [
    {
        id: 1,
        title: "Albergue Utaja",
        description: "Ofrecemos un hogar seguro y cálido para familias durante el tratamiento oncológico de sus hijos.",
        image: null,
        color: "bg-verde-lima"
    },
    {
        id: 2,
        title: "Alimentación y Nutrición",
        description: "Programa integral de nutrición y apoyo alimentario para los niños en tratamiento.",
        image: null,
        color: "bg-amarillo-detalle"
    }
];

const FALLBACK_NEWS = [
    {
        id: 1,
        title: "Nueva iniciativa de apoyo",
        content: "Lanzamos una nueva iniciativa para ampliar nuestro alcance en la comunidad.",
        image: null,
        date: new Date().toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })
    }
];

const FALLBACK_TESTIMONIALS = [
    {
        id: 1,
        name: "Familia García",
        role: "Beneficiarios",
        message: "La fundación ha sido fundamental en nuestro camino de recuperación.",
        image: null,
        age: "8 años"
    }
];

// --- 1. ENDPOINT DE PROGRAMAS ---
export const fetchPrograms = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/programs`);
        // Return fallback if no data
        return response.data && response.data.length > 0 ? response.data : FALLBACK_PROGRAMS;
    } catch (error) {
        console.error("Error fetching programs:", error);
        return FALLBACK_PROGRAMS; // Return fallback data on error
    }
};

// --- 2. ENDPOINT DE NOTICIAS ---
export const fetchNews = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/news`);
        return response.data && response.data.length > 0 ? response.data : FALLBACK_NEWS;
    } catch (error) {
        console.error("Error fetching news:", error);
        return FALLBACK_NEWS;
    }
};

// --- 3. ENDPOINT DE TESTIMONIOS ---
export const fetchTestimonials = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/testimonials`);
        return response.data && response.data.length > 0 ? response.data : FALLBACK_TESTIMONIALS;
    } catch (error) {
        console.error("Error fetching testimonials:", error);
        return FALLBACK_TESTIMONIALS;
    }
};

// ... También puedes añadir fetchContact y fetchSubscribe aquí ...