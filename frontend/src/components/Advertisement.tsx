// components/Advertisements.tsx
"use client";
import React, { useEffect, useState } from 'react';

const Advertisements = () => {
  const [ads, setAds] = useState<any[]>([]);

  useEffect(() => {
    fetch('http://127.0.0.1:8000/api/advertisements')
      .then(res => res.json())
      .then(data => setAds(data))
      .catch(err => console.error("Error cargando anuncios:", err));
  }, []);

  if (ads.length === 0) return null; // No mostrar nada si no hay anuncios

  return (
    <div className="bg-yellow-100 border-b border-yellow-200 py-3">
      <div className="container mx-auto px-6">
        {ads.map((ad) => (
          <div key={ad.id} className="flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
              <h3 className="font-bold text-yellow-800">{ad.title}</h3>
              <p className="text-sm text-yellow-700">{ad.description}</p>
            </div>
            
            {ad.link_url && (
              <a 
                href={ad.link_url}
                target="_blank"
                className="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
              >
                Más información
              </a>
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default Advertisements;