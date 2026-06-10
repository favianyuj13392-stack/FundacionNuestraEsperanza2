const DEFAULT_BACKEND_URL = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000';

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
