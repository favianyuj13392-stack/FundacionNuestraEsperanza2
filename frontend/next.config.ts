import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  // Salida standalone para contenedor Docker Node.js
  output: 'standalone',

  images: {
    // Si necesitas optimización de Next.js, puedes dejar esto como false o quitarlo
    // unoptimized: true, 
    
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'api.fundacion-nuestra-esperanza.cloud', // Dominio de producción
        pathname: '/storage/**',
      },
      // Dominios para entorno local:
      {
        protocol: 'http',
        hostname: '127.0.0.1',
        port: '8000',
        pathname: '/storage/**',
      },
      {
        protocol: 'http',
        hostname: 'localhost',
        port: '8000',
        pathname: '/storage/**',
      },
      {
        protocol: 'https',
        hostname: 'res.cloudinary.com',
        pathname: '/**',
      }
    ],
  },
};

export default nextConfig;
