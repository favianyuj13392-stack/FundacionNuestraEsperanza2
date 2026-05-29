export function normalizeApiBaseUrl(rawUrl: string | undefined) {
  const initial = (rawUrl ?? 'http://127.0.0.1:8000').trim();
  let normalized = initial.replace(/\/+$/, '');

  if (normalized.toLowerCase().endsWith('/api')) {
    normalized = normalized.slice(0, -4).replace(/\/+$/, '');
  }

  return normalized || 'http://127.0.0.1:8000';
}

export const API_BASE_URL = normalizeApiBaseUrl(process.env.NEXT_PUBLIC_API_BASE_URL);
