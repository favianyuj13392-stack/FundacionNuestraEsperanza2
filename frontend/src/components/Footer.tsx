"use client";
import React, { useState,useEffect } from 'react';
import Image from 'next/image';
import Link from 'next/link';

// 1. DEFINIMOS QUÉ RECIBE EL COMPONENTE (La "puerta de entrada")
interface FooterProps {
  onOpenDonationModal?: () => void; 
}

// 2. RECIBIMOS LA FUNCIÓN AQUÍ
// Si no nos pasan nada, usamos una función vacía () => {} para que no falle.
const Footer: React.FC<FooterProps> = ({ onOpenDonationModal = () => {} }) => {
  
  // --- Lógica de Suscripción (Integrada aquí también) ---
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');
  const API_URL = 'http://127.0.0.1:8000/api';

  const [settings, setSettings] = useState<any>(null);
  const [navLinks, setNavLinks] = useState<any[]>([]);

  useEffect(() => {
    //Cargar settings
    fetch(`${API_URL}/settings`)
      .then(res => res.json())
      .then(data => setSettings(data))
      .catch(err => console.error("Error cargando settings en Footer:", err));
    //Cargar enlaces del menú
    fetch(`${API_URL}/nav-links`)
      .then(res => res.json())
      .then(data => {
        // Filtramos solo los links que deben ir en el footer (o en ambos)
        const footerLinks = data.filter((link: any) => link.location === 'footer' || link.location === 'both');
        setNavLinks(footerLinks);
      })
      .catch(err => console.error("Error cargando nav-links en Footer:", err));
  }, []);
  // Preparamos la URL del logo para el footer
  const footerLogoUrl = settings?.global_logo 
    ? `http://127.0.0.1:8000/storage/${settings.global_logo}` 
    : "/IMG/Logo.jpg";

  const handleSubscribe = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus('loading');
    try {
      const response = await fetch(`${API_URL}/subscribe`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email }),
      });
      if (response.ok) {
        setStatus('success');
        setEmail('');
        setTimeout(() => setStatus('idle'), 3000);
      } else {
        setStatus('error');
      }
    } catch (error) {
      console.error("Error al suscribirse:", error);
      setStatus('error');
      setTimeout(() => setStatus('idle'), 3000);
    }
  };
  // -----------------------------------------------------
  // Enlaces por defecto en caso de que Filament esté vacío por ahora
  const defaultLinks = [
    { title: "Inicio", url: "/" },
    { title: "Quiénes Somos", url: "/quienes-somos" },
    { title: "Programas", url: "/programas" },
    { title: "Cómo Ayudar", url: "/como-ayudar" },
    { title: "Noticias", url: "/news" },
  ];
  const linksToDisplay = navLinks.length > 0 ? navLinks : defaultLinks;
  // Enlace de WhatsApp para Voluntarios
  const phoneNumber = "59170112236"; 
  const whatsappUrl = `https://wa.me/${phoneNumber}?text=Hola,%20quisiera%20ser%20voluntario%20de%20la%20fundación.`;

  return (
    <footer className="bg-azul-marino text-white pt-16 pb-8">
      <div className="container mx-auto px-6">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8 mb-8">
          
          {/* Logo */}
          <div className="flex items-center sm:col-span-2 lg:col-span-1 justify-center lg:justify-start">
            <div className="relative w-48 h-48">
                <Image 
                  src={footerLogoUrl} 
                  alt="Logo Fundación Nuestra Esperanza" 
                  width={160} 
                  height={60} 
                  className="object-contain rounded-lg p-2 mb-4" 
                />
            </div>
          </div>
          
          {/* Info */}
          <div className="sm:col-span-2 lg:col-span-1">
            <h3 className="font-bold mb-4 font-title text-xl">FUNDACIÓN NUESTRA ESPERANZA</h3>
            <p className="text-gray-300 text-sm mb-4 font-sans leading-relaxed">
              {settings?.footer_about_text || 'Haciendo una diferencia en la vida de los niños que padecen de cáncer en toda Bolivia.'}
            </p>
            {/* Redes Sociales */}
            <div className="flex space-x-4">
              {settings?.social_facebook && (
                <a href={settings.social_facebook} target="_blank" rel="noreferrer" className="hover:scale-110 transition-transform">
                  <Image src="/IMG/ic_facebook.png" alt='Facebook' width={30} height={30} className="bg-white rounded-full p-1" />
                </a>
              )}
              {settings?.social_instagram && (
                <a href={settings.social_instagram} target="_blank" rel="noreferrer" className="hover:scale-110 transition-transform">
                  <Image src="/IMG/ic_tiktok.png" alt='TikTok' width={30} height={30} className="bg-white rounded-full p-1" />
                </a>
              )}
              {settings?.social_tiktok && (
                <a href={settings.social_tiktok} target="_blank" rel="noreferrer" className="hover:scale-110 transition-transform">
                  <Image src="/IMG/ic_instagram.png" alt='Instagram' width={30} height={30} className="bg-white rounded-full p-1" />
                </a>
              )}
            </div>
          </div>

          {/* Menú Rápido */}
          <div>
            <h4 className="font-bold mb-4 text-lg">Menú Rápido</h4>
            <ul className="space-y-2 text-sm font-sans">
              {linksToDisplay.map((link, index) => (
                <li key={index}>
                  <Link href={link.url} className="hover:text-rosa-principal transition duration-300">
                    {link.title}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Cómo Ayudar */}
          <div>
            <h4 className="font-bold mb-4 text-lg">Cómo Ayudar</h4>
            <ul className="space-y-4 text-sm">
              <li>
                {/* 3. AQUÍ USAMOS LA FUNCIÓN EN EL BOTÓN */}
                <button 
                    onClick={onOpenDonationModal}
                    className="w-full bg-rosa-principal px-5 py-3 rounded-full font-bold hover:bg-amarillo-detalle transition duration-300 font-button text-white shadow-md hover:shadow-lg"
                >
                    DONAR AHORA
                </button>
              </li>
              <li>
                <a 
                    href={whatsappUrl} 
                    target="_blank"
                    rel="noopener noreferrer"
                    className="w-full inline-block text-center bg-white text-azul-marino px-5 py-3 rounded-full font-bold hover:bg-gray-100 transition duration-300 font-button shadow-md"
                >
                    SER VOLUNTARIO
                </a>
              </li>
            </ul>
          </div>

          {/* Suscríbete */}
          <div>
            <h4 className="font-bold mb-4 text-lg">Suscríbete</h4>
            <p className="text-gray-300 text-sm mb-3 font-sans">Recibe noticias en tu correo.</p>
            
            <form onSubmit={handleSubscribe} className="flex flex-col gap-2">
              <div className="flex">
                <input 
                    type="email" 
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="Tu correo" 
                    required
                    className="w-full rounded-l-md px-3 py-2 text-gray-800 focus:outline-none text-sm" 
                />
                <button 
                    type="submit"
                    disabled={status === 'loading' || status === 'success'}
                    className={`px-4 rounded-r-md font-bold transition-colors text-sm
                        ${status === 'success' ? 'bg-green-500' : 'bg-rosa-principal hover:bg-amarillo-detalle'}
                    `}
                >
                    {status === 'loading' ? '...' : status === 'success' ? '✓' : 'OK'}
                </button>
              </div>
              {status === 'error' && <p className="text-xs text-red-400 mt-1">Error al suscribir.</p>}
              {status === 'success' && <p className="text-xs text-green-400 mt-1">¡Suscrito!</p>}
            </form>
          </div>
        </div>

        <div className="text-center text-gray-400 text-sm pt-8 border-t border-gray-700 font-sans">
          &copy; {new Date().getFullYear()} Fundación Nuestra Esperanza. Todos los derechos reservados.
        </div>
      </div>
    </footer>
  );
};

export default Footer;