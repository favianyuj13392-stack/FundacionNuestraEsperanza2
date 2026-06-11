// frontend/src/services/dataService.js (o .ts)

import axios from 'axios';
import { API_BASE_URL } from '@/utils/apiBaseUrl';

// En frontend/src/services/DataService.ts
// Usar la URL base normalizada y evitar duplicar /api

// --- 1. ENDPOINT DE PROGRAMAS ---
export const fetchPrograms = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/programs`);
        return response.data; // Devuelve el array de programas
    } catch (error) {
        console.error("Error fetching programs:", error);
        return []; // Devuelve un array vacío en caso de error
    }
};

// --- 2. ENDPOINT DE NOTICIAS ---
export const fetchNews = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/news`);
        return response.data; // Devuelve el array de noticias
    } catch (error) {
        console.error("Error fetching news:", error);
        return [];
    }
};

// --- 3. ENDPOINT DE TESTIMONIOS ---
export const fetchTestimonials = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/testimonials`);
        return response.data; // Devuelve el array de testimonios
    } catch (error) {
        console.error("Error fetching testimonials:", error);
        return [];
    }
};

// ... También puedes añadir fetchContact y fetchSubscribe aquí ...