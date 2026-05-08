import { createContext, useContext, useState, useCallback } from 'react';
import type { LoadingContextType } from '../types/types';

const LoadingContext = createContext<LoadingContextType | null>(null);

export const useLoading = (): LoadingContextType => {
    const context = useContext(LoadingContext);
    if (!context) {
        throw new Error('useLoading must be used within LoadingProvider');
    }
    return context;
};

export const LoadingProvider = ({ children }: { children: React.ReactNode }) => {
    const [isLoading, setIsLoading] = useState<boolean>(false);

    const startLoading = useCallback((): void => {
        setIsLoading(true);
    }, []);

    const stopLoading = useCallback((): void => {
        setIsLoading(false);
    }, []);

    const value: LoadingContextType = {
        isLoading,
        startLoading,
        stopLoading
    };

    // @ts-ignore
    return (
        <LoadingContext.Provider value={value}>
            {children}
        </LoadingContext.Provider>
    );
};
