export function normalizeApiBaseUrl(rawUrl: string | undefined) {
  // Always return production URL if process.env.NODE_ENV is production
  if (process.env.NODE_ENV === 'production') {
    return 'https://api.fundacion-nuestra-esperanza.cloud';
  }

  const initial = (rawUrl ?? 'http://127.0.0.1:8000').trim();
  let normalized = initial.replace(/\/+$/, '');

  if (normalized.toLowerCase().endsWith('/api')) {
    normalized = normalized.slice(0, -4).replace(/\/+$/, '');
  }

  return normalized || 'http://127.0.0.1:8000';
}

// In production, we don't rely on NEXT_PUBLIC_API_BASE_URL because Docker env injection is failing
export const API_BASE_URL = process.env.NODE_ENV === 'production' 
  ? 'https://api.fundacion-nuestra-esperanza.cloud/api' 
  : normalizeApiBaseUrl(process.env.NEXT_PUBLIC_API_BASE_URL);
