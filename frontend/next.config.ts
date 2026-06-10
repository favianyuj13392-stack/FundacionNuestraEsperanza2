import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Si estás exportando estáticamente (para Hostinger compartido):
  output: 'export',

  images: {
    unoptimized: true, // Necesario para 'next export'
    // Añadir los dominios donde se alojan tus imágenes
    domains: ['127.0.0.1', 'localhost'],
    remotePatterns: [
      {
        protocol: 'http',
        hostname: '127.0.0.1', // Permite imágenes de tu IP local
        port: '8000',          // El puerto de Laravel
      },
      {
        protocol: 'http',
        hostname: 'localhost', // Por si acaso
        port: '8000',
        pathname: '/storage/**', // Ruta de almacenamiento
      },
      {
        protocol: 'https',
        hostname: 'res.cloudinary.com',
        pathname: '/**',
      },
      // Cuando subas a Hostinger, agrega aquí tu dominio real:
      // { protocol: 'https', hostname: 'tufundacion.org', ... }
    ],
  },
};

export default nextConfig;
