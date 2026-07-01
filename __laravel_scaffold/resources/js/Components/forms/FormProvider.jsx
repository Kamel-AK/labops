import { FormProvider as ReactHookFormProvider } from 'react-hook-form';

export default function FormProvider({ children, ...form }) {
    return <ReactHookFormProvider {...form}>{children}</ReactHookFormProvider>;
}
