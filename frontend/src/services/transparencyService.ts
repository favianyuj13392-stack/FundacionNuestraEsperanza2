import axios from 'axios';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://127.0.0.1:8000/api';

export interface TransparencyCampaign {
    id: number;
    name: string;
    slug: string;
    description: string;
    image_path: string | null;
    monetary_goal: number;
    total_recaudado: number;
    progress_percentage: number;
    start_date: string | null;
    end_date: string | null;
}

export interface Expense {
    date: string;
    description: string;
    amount: number | string;
}

export interface TransparencyDetail {
    metadata: {
        name: string;
        status: string;
        monetary_goal: number | string;
        report_pdf_url: string | null;
    };
    cifras: {
        total_recaudado: number;
        total_ejecutado: number;
        saldo_disponible: number;
    };
    trazabilidad: Expense[];
    donaciones_recientes?: any[];
}

export const fetchTransparencyCampaigns = async (): Promise<TransparencyCampaign[]> => {
    try {
        const response = await axios.get(`${API_BASE_URL}/transparency`);
        return response.data?.data || [];
    } catch (error) {
        console.error("Error fetching transparency campaigns:", error);
        return [];
    }
};

export const fetchTransparencyDetail = async (slug: string): Promise<TransparencyDetail | null> => {
    try {
        const response = await axios.get(`${API_BASE_URL}/transparency/${slug}`);
        return response.data?.data || null;
    } catch (error) {
        console.error(`Error fetching transparency detail for ${slug}:`, error);
        return null;
    }
};
