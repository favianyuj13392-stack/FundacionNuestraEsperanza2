/* eslint-disable @typescript-eslint/no-explicit-any */
import axios from 'axios';

import { API_BASE_URL } from '@/utils/apiBaseUrl';

const API_URL = API_BASE_URL;

// Configure axios to include auth token
const getAuthHeaders = () => {
  const token = localStorage.getItem('token');
  return token ? { Authorization: `Bearer ${token}` } : {};
};

export interface DonationTier {
    id: number;
    amount: string;
    label: string;
    currency_id: number;
}

export interface QrResponse {
    qr_image: string;
    qr_id: string;
    expiration: string;
    mock?: boolean;
}

export interface Donation {
    id: number;
    amount: string;
    status: string;
    date: string;
    certificate_url?: string;
    qr?: {
        code: string;
    }
}

export const donationService = {
    /**
     * Get available donation options
     */
    getOptions: async (): Promise<DonationTier[]> => {
        const response = await axios.get<DonationTier[]>(`${API_URL}/api/public/donation-options`, {
            headers: getAuthHeaders()
        });
        return response.data;
    },

    getCampaigns: async (): Promise<any[]> => {
        const response = await axios.get(`${API_URL}/api/public/campaigns`, {
            headers: getAuthHeaders()
        });
        return (Array.isArray(response.data) ? response.data : ((response.data as any)?.data || [])) as any[];
    },

    /**
     * Request a QR for donation
     */
    requestQr: async (
        tierId?: number, 
        customAmount?: number, 
        isAnonymous: boolean = true, 
        donorDetails?: { name: string, ci: string, phone: string },
        campaignId?: number
    ): Promise<QrResponse> => {
        const payload: Record<string, string | number | boolean> = {
            is_anonymous: isAnonymous
        };
        
        if (tierId) payload.tier_id = tierId;
        else if (customAmount) payload.custom_amount = customAmount;
        
        if (campaignId) payload.campaign_id = campaignId;

        if (!isAnonymous && donorDetails) {
            payload.donor_name = donorDetails.name;
            payload.donor_ci = donorDetails.ci;
            payload.donor_phone = donorDetails.phone;
        }

        // Retrieve token from localStorage manually since we are outside React Context
        const token = typeof window !== 'undefined' ? localStorage.getItem('token') : null;
        const config = token ? { headers: { Authorization: `Bearer ${token}` } } : {};

        const response = await axios.post<QrResponse>(`${API_URL}/api/public/request-qr`, payload, {
            headers: getAuthHeaders()
        });
        return response.data;
    },

    /**
     * Check status of a QR
     */
    checkStatus: async (qrId: string): Promise<{ status: string }> => {
        const response = await axios.get<{ status: string }>(`${API_URL}/api/public/check-status/${qrId}`, {
            headers: getAuthHeaders()
        });
        return response.data;
    },

    /**
     * Request a Domiciliacion QR (Monthly subscription)
     */
    requestSubscriptionQr: async (
        amount: number,
        donorDetails: { name: string, email: string, address?: string, phone_number?: string },
        campaignId?: number
    ): Promise<QrResponse> => {
        const payload: Record<string, string | number> = {
            amount: amount,
            name: donorDetails.name,
            email: donorDetails.email,
            phone_number: donorDetails.phone_number || '',
            address: donorDetails.address || '',
        };
        if (campaignId) payload.campaign_id = campaignId;

        const response = await axios.post(`${API_URL}/api/subscriptions/domiciliacion`, payload, {
            headers: getAuthHeaders()
        });
        
        // Map backend response structure to QrResponse
        const data = (response.data as any).data;
        return {
            qr_image: data.qr_image_base64,
            qr_id: data.subscription_id.toString(), // Using subscription_id for polling
            expiration: '', // Not strictly needed
        };
    },

    /**
     * Check status of a subscription
     */
    checkSubscriptionStatus: async (subscriptionId: string): Promise<{ status: string }> => {
        const response = await axios.get<{ data: { is_enrolled: boolean, status: string } }>(`${API_URL}/api/subscriptions/domiciliacion/${subscriptionId}/status`, {
            headers: getAuthHeaders()
        });
        return { status: response.data.data.is_enrolled ? 'paid' : 'pending' };
    },

    /**
     * Get logged-in user's donations
     */
    getMyDonations: async (): Promise<Donation[]> => {
        const response = await axios.get(`${API_URL}/api/auth/donations/my`, {
            headers: getAuthHeaders()
        });
        
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        const body = response.data as any;
        if (Array.isArray(body)) return body;
        if (body && Array.isArray(body.data)) return body.data;
        return [];
    },

    /**
     * Simulate a payment (Demo Mode Only)
     * Calls the webhook directly to force 'paid' status
     */
    simulatePayment: async (qrId: string, donorName: string = 'Simulated Donor') => {
        // Construct dummy webhook payload
        const payload = {
            QRId: qrId,
            VoucherId: 'sim_' + Math.floor(Math.random() * 100000),
            originName: donorName,
            TransactionDateTime: new Date().toISOString(),
            Gloss: 'Simulated Payment'
        };

        const response = await axios.post(`${API_URL}/api/webhooks/bnb`, payload);
        return response.data;
    }
};
