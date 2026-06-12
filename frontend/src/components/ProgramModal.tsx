"use client";
import React from 'react';
import Image from 'next/image';
import { motion, AnimatePresence } from 'framer-motion';

interface Program {
  id: number;
  title: string;
  description: string;
  image: string;
  color: string;
}

interface ProgramModalProps {
  program: Program | null;
  isOpen: boolean;
  onClose: () => void;
}

const ProgramModal: React.FC<ProgramModalProps> = ({ program, isOpen, onClose }) => {
  if (!isOpen || !program) return null;

  return (
    <AnimatePresence>
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        exit={{ opacity: 0 }}
        onClick={onClose}
        className="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
      >
        <motion.div
          initial={{ scale: 0.9, opacity: 0, y: 20 }}
          animate={{ scale: 1, opacity: 1, y: 0 }}
          exit={{ scale: 0.9, opacity: 0, y: 20 }}
          onClick={(e) => e.stopPropagation()} // Evita cerrar si clickeas dentro
          className="bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-5xl flex flex-col md:flex-row max-h-[90vh]"
        >
          {/* LADO IZQUIERDO: IMAGEN (45% del ancho) */}
          <div className="w-full md:w-[45%] relative h-64 md:h-auto bg-gradient-to-br from-celeste-fondo to-azul-marino flex items-center justify-center">
            {program.image ? (
                <Image
                  src={program.image}
                  alt={program.title}
                  fill
                  className="object-cover"
                  unoptimized
                />
            ) : (
                <div className="text-white/50 flex flex-col items-center justify-center h-full">
                    <span className="font-semibold font-sans mt-2 text-xl">Sin imagen</span>
                </div>
            )}
            {/* Franja de color decorativa */}
            <div className={`absolute top-0 left-0 w-full h-2 z-10 ${program.color || 'bg-rosa-principal'}`}></div>
          </div>

          {/* LADO DERECHO: CONTENIDO (55% del ancho) */}
          <div className="w-full md:w-[55%] p-8 md:p-10 overflow-y-auto relative flex flex-col">
            <button
              onClick={onClose}
              className="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <h2 className="text-3xl md:text-4xl font-bold font-title text-azul-marino mb-6 pr-8">
              {program.title}
            </h2>

            <div 
              className="text-gray-700 font-sans text-lg leading-relaxed space-y-4"
              dangerouslySetInnerHTML={{ __html: program.description }}
            />

            <div className="mt-8 pt-6 border-t border-gray-100 flex justify-end">
              <button
                onClick={onClose}
                className="bg-turquesa-secundario text-white px-6 py-2 rounded-full font-bold hover:bg-azul-marino transition duration-300 font-button"
              >
                CERRAR
              </button>
            </div>
          </div>
        </motion.div>
      </motion.div>
    </AnimatePresence>
  );
};

export default ProgramModal;