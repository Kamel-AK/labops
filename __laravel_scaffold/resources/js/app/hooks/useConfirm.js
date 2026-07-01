import { useConfirmContext } from '@/app/contexts/ConfirmContext';

export function useConfirm() {
    return useConfirmContext().confirm;
}
