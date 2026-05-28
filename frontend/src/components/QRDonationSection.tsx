"use client";
import React from 'react';
import DonationForm from "@/components/DonationForm";

const QRDonationSection = () => {
  return (
    <section className="bg-gray-50 py-16 text-center">
      <div className="container mx-auto px-4">
        <h2 className="text-3xl md:text-4xl font-bold text-azul-marino mb-8 font-title">
          Haz una Donación con QR (BNB)
        </h2>
        <div className="flex justify-center">
           <DonationForm />
        </div>
      </div>
    </section>
  );
};

export default QRDonationSection;