"use client";
import React, { useEffect } from 'react';
import { useRouter } from 'next/navigation';

interface DonationModalProps {
  isOpen: boolean;
  onClose: () => void;
  campaignId?: number;
}

const DonationModal: React.FC<DonationModalProps> = ({ isOpen, onClose, campaignId }) => {
  const router = useRouter();

  useEffect(() => {
    if (isOpen) {
      const search = typeof window !== 'undefined' ? window.location.search : '';
      let url = '/donar' + search;
      if (!search && campaignId) {
        url = `/donar?campaign_id=${campaignId}`;
      }
      router.push(url);
      onClose();
    }
  }, [isOpen, campaignId, router, onClose]);

  return null;
};

export default DonationModal;