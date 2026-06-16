import { API_BASE_URL } from './apiBaseUrl';

const DEFAULT_BACKEND_URL = API_BASE_URL;

export const resolveImageUrl = (value: string | null | undefined, fallback = '/placeholder-image.jpg') => {
  if (!value) return fallback;

  const trimmed = value.trim();
  if (!trimmed) return fallback;

  if (trimmed.startsWith('http://') || trimmed.startsWith('https://') || trimmed.startsWith('data:')) {
    return trimmed;
  }

  if (trimmed.startsWith('/')) {
    return trimmed;
  }

  if (trimmed.startsWith('storage/')) {
    return `${DEFAULT_BACKEND_URL}/${trimmed}`;
  }

  return `${DEFAULT_BACKEND_URL}/storage/${trimmed}`;
};
