"use client";
import React from 'react';
import Image from 'next/image';

interface DirectDonationsProps {
  onOpenDonationModal: () => void;
}

const DirectDonations: React.FC<DirectDonationsProps> = ({ onOpenDonationModal }) => {
  return (
    <section className="py-16 container mx-auto px-6">
      <div className="grid md:grid-cols-2 gap-12 items-center">
        <div>
          <h2 className="text-3xl font-bold text-azul-marino mb-6 font-title">Donaciones Directas</h2>
          <p className="text-gray-600 mb-6 font-sans">
            Puedes realizar un depósito bancario directo a nuestras cuentas oficiales. 
            Cada centavo se destina a alimentación, medicinas y mantenimiento del albergue.
          </p>
          
          <div className="bg-beige-claro p-6 rounded-lg border-l-4 border-rosa-principal shadow-md">
            <h3 className="font-bold text-lg mb-2 text-azul-marino">Banco Nacional de Bolivia (BNB)</h3>
            <p className="text-gray-700">Cuenta en Bolivianos:</p>
            <p className="text-2xl font-bold text-rosa-principal mb-2">150-0234567</p>
            <p className="text-sm text-gray-500">Nit: 1234567015</p>
            <p className="text-sm text-gray-500">Razón Social: Fundación Nuestra Esperanza</p>
          </div>

          <div className="mt-8">
            <button 
              onClick={onOpenDonationModal}
              className="bg-turquesa-secundario text-white px-8 py-3 rounded-full font-bold hover:bg-azul-marino transition duration-300 font-button shadow-lg hover:shadow-xl"
            >
              DONAR CON QR ONLINE
            </button>
          </div>
        </div>
        <div className="relative h-80 w-full rounded-xl overflow-hidden shadow-2xl">
          <Image 
            src="/IMG/Donación .jpg"
            alt="Donación" 
            fill
            className="object-cover"
          />
        </div>
      </div>
    </section>
  );
};

export default DirectDonations;