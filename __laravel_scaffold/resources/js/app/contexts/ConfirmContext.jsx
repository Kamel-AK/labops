import ConfirmationDialog from '@/components/feedback/ConfirmationDialog';
import { createContext, useCallback, useContext, useState } from 'react';

const ConfirmContext = createContext({
    confirm: async () => false,
});

export function ConfirmProvider({ children }) {
    const [options, setOptions] = useState(null);

    const confirm = useCallback((nextOptions = {}) => {
        return new Promise((resolve) => {
            setOptions({ ...nextOptions, resolve });
        });
    }, []);

    const close = useCallback(
        (value) => {
            options?.resolve(value);
            setOptions(null);
        },
        [options],
    );

    return (
        <ConfirmContext.Provider value={{ confirm }}>
            {children}
            <ConfirmationDialog
                description={options?.description}
                open={Boolean(options)}
                title={options?.title}
                onCancel={() => close(false)}
                onConfirm={() => close(true)}
                onOpenChange={(open) => {
                    if (!open) {
                        close(false);
                    }
                }}
            />
        </ConfirmContext.Provider>
    );
}

export function useConfirmContext() {
    return useContext(ConfirmContext);
}
