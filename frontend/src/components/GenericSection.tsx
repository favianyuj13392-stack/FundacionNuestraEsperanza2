"use client";
import React from 'react';
import Image from 'next/image';
import { motion } from 'framer-motion';

interface GenericSectionProps {
  data: {
    id: number;
    identifier: string;
    name: string;
    title?: string;
    subtitle?: string;
    content?: string;
    image?: string | null;
    is_active?: boolean;
    order?: number;
  };
}

const sectionStyles = [
  {
    wrapper: 'bg-rosa-claro/10',
    accent: 'from-rosa-principal to-amarillo-detalle',
    badge: 'bg-rosa-principal text-white',
    imageFrame: 'bg-gradient-to-br from-rosa-principal/20 to-amarillo-detalle/20',
  },
  {
    wrapper: 'bg-celeste-claro/10',
    accent: 'from-turquesa-secundario to-celeste-claro',
    badge: 'bg-turquesa-secundario text-white',
    imageFrame: 'bg-gradient-to-br from-turquesa-secundario/20 to-celeste-claro/20',
  },
  {
    wrapper: 'bg-verde-lima-claro/10',
    accent: 'from-verde-lima to-azul-marino',
    badge: 'bg-verde-lima text-white',
    imageFrame: 'bg-gradient-to-br from-verde-lima/20 to-azul-marino/20',
  },
  {
    wrapper: 'bg-amarillo-claro/15',
    accent: 'from-amarillo-detalle to-rosa-principal',
    badge: 'bg-amarillo-detalle text-white',
    imageFrame: 'bg-gradient-to-br from-amarillo-detalle/20 to-rosa-principal/20',
  },
];

const GenericSection: React.FC<GenericSectionProps> = ({ data }) => {
  const styleIndex = data.order ? (data.order - 1) % sectionStyles.length : Math.abs(data.identifier.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0)) % sectionStyles.length;
  const styles = sectionStyles[styleIndex];

  return (
    <motion.section
      initial={{ opacity: 0, y: 30 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.25 }}
      transition={{ duration: 0.8, ease: 'easeOut' }}
      className={`relative overflow-hidden py-20 ${styles.wrapper}`}
    >
      <div className="absolute right-0 top-10 h-40 w-40 rounded-full blur-3xl bg-white/20" />
      <div className="absolute left-0 bottom-0 h-60 w-60 rounded-full blur-3xl bg-white/10" />
      <div className="container mx-auto px-6">
        <div className="grid gap-10 lg:grid-cols-[1.1fr_0.9fr] items-center">
          <div>
            <span className={`inline-block text-sm font-bold uppercase tracking-[0.35em] px-3 py-2 rounded-full ${styles.badge} mb-4`}>
              {data.name}
            </span>
            <h2 className="text-4xl md:text-5xl font-bold text-azul-marino mb-6 font-title">
              {data.title || data.name}
            </h2>
            {data.subtitle && (
              <p className="text-xl text-azul-marino/75 mb-6">{data.subtitle}</p>
            )}
            {data.content ? (
              <div
                className="max-w-3xl text-gray-700 leading-relaxed space-y-5"
                dangerouslySetInnerHTML={{ __html: data.content }}
              />
            ) : (
              <p className="text-gray-600 max-w-3xl">Esta sección no tiene contenido definido todavía.</p>
            )}
          </div>

          {data.image ? (
            <motion.div
              initial={{ opacity: 0, scale: 0.95 }}
              whileHover={{ scale: 1.02 }}
              whileInView={{ opacity: 1, scale: 1 }}
              viewport={{ once: true, amount: 0.3 }}
              transition={{ duration: 0.6 }}
              className={`relative h-80 overflow-hidden rounded-[2rem] border border-rosa-principal/10 shadow-xl ${styles.imageFrame}`}
            >
              <Image
                src={data.image}
                alt={data.title || data.name}
                fill
                className="object-cover"
                unoptimized
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent" />
            </motion.div>
          ) : (
            <div className="flex h-80 items-center justify-center rounded-[2rem] border border-rosa-principal/10 bg-rosa-claro/20 shadow-xl">
              <span className="text-lg font-semibold text-rosa-principal">Imagen no disponible</span>
            </div>
          )}
        </div>
      </div>
    </motion.section>
  );
};

export default GenericSection;
