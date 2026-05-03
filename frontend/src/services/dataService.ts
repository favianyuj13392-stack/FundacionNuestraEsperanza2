// frontend/src/services/dataService.js (o .ts)

import axios from 'axios';

// Usar process.env en lugar de import.meta.env
const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://127.0.0.1:8000/api'; 

// Configure axios to include auth token
const getAuthHeaders = () => {
  const token = typeof window !== 'undefined' ? localStorage.getItem('token') : null;
  return token ? { Authorization: `Bearer ${token}` } : {};
};

// --- 1. ENDPOINT DE PROGRAMAS ---
export const fetchPrograms = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/programs`, {
            headers: getAuthHeaders()
        });
        return response.data; // Devuelve el array de programas
    } catch (error) {
        console.error("Error fetching programs:", error);
        return []; // Devuelve un array vacío en caso de error
    }
};

// --- 2. ENDPOINT DE NOTICIAS ---
export const fetchNews = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/news`, {
            headers: getAuthHeaders()
        });
        return response.data; // Devuelve el array de noticias
    } catch (error) {
        console.error("Error fetching news:", error);
        return [];
    }
};

// --- 3. ENDPOINT DE TESTIMONIOS ---
export const fetchTestimonials = async () => {
    try {
        const response = await axios.get(`${API_BASE_URL}/testimonials`, {
            headers: getAuthHeaders()
        });
        return response.data; // Devuelve el array de testimonios
    } catch (error) {
        console.error("Error fetching testimonials:", error);
        return [];
    }
};

// ... También puedes añadir fetchContact y fetchSubscribe aquí ...