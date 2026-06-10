export const cloudinaryLoader = ({ src, width, quality }: { src: string; width?: number; quality?: number }) => {
  const base = process.env.NEXT_PUBLIC_CLOUDINARY_BASE_URL || 'https://res.cloudinary.com/your-cloud-name/image/upload';
  if (!src || src.trim() === '') return null as any;
  // If src is already absolute, return it unchanged (assume it's a cloudinary or full URL)
  if (/^https?:\/\//i.test(src)) return src;

  // Otherwise prefix with Cloudinary base
  const q = quality ? `,q_${quality}` : '';
  const w = width ? `,w_${width}` : '';
  // Note: using simple concatenation; users can set NEXT_PUBLIC_CLOUDINARY_BASE_URL to include transformations
  return `${base}${w}${q}/${src}`;
};
