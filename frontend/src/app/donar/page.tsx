import React from 'react';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import DonationForm from '@/components/DonationForm';

export default function DonarPage() {
    return (
        <div className="min-h-screen flex flex-col">
            <Navbar />
            <main className="flex-grow bg-gray-50 pt-32 pb-12">
                <div className="container mx-auto px-4">
                    <DonationForm />
                </div>
            </main>
            <Footer />
        </div>
    );
}
