import { cn } from '@/app/lib/utils';
import { useDropzone } from 'react-dropzone';

export default function Dropzone({ children, className, ...options }) {
    const { getInputProps, getRootProps, isDragActive } = useDropzone(options);

    return (
        <div
            {...getRootProps()}
            className={cn(
                'flex min-h-32 cursor-pointer items-center justify-center rounded-lg border border-dashed p-6 text-sm text-muted-foreground transition-colors hover:bg-muted/50',
                isDragActive &&
                    'border-secondary bg-secondary/10 text-foreground',
                className,
            )}
        >
            <input {...getInputProps()} />
            {children}
        </div>
    );
}
