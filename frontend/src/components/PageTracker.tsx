'use client';

import { useEffect } from 'react';
import { usePathname } from 'next/navigation';

export default function PageTracker() {
  const pathname = usePathname();

  useEffect(() => {
    // Si no hay pathname, salimos
    if (!pathname) return;

    // Disparar la llamada al backend para registrar la visita
    const trackVisit = async () => {
      try {
        await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'}/api/track-visit`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            url: pathname,
          }),
        });
      } catch (error) {
        console.error('Error tracking page visit:', error);
      }
    };

    trackVisit();
  }, [pathname]);

  return null; // Este componente no renderiza nada visible
}
