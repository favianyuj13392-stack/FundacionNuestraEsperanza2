"use client";
import React, { Suspense } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import DonationForm from './DonationForm'; // Reusing the unified logic

interface DonationModalProps {
  isOpen: boolean;
  onClose: () => void;
  campaignId?: number;
}

const DonationModal: React.FC<DonationModalProps> = ({ isOpen, onClose, campaignId }) => {
  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          className="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4"
          onClick={onClose}
        >
          <motion.div
            initial={{ scale: 0.9, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            exit={{ scale: 0.9, opacity: 0 }}
            transition={{ duration: 0.3, ease: 'easeOut' }}
            className="w-full max-w-md relative"
            onClick={(e) => e.stopPropagation()} // Prevent click bubbling
          >
             {/* We simply render the DonationForm which now handles the internal UI 
                 and accepts isInModal to adjust removing shadows or adding specific modal headers if needed.
             */}
             <Suspense fallback={<div className="bg-white p-8 rounded-xl text-center">Cargando formulario...</div>}>
               <DonationForm onClose={onClose} isInModal={true} campaignIdProp={campaignId} />
             </Suspense>
             
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
};

export default DonationModal;