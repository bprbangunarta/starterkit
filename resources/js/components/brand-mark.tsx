import { useBrand } from '@/lib/brand-store';
import { cn } from '@/lib/utils';

/** The logo and the app name, for the screens outside the app shell (sign-in, error). */
export function BrandMark({ className }: { className?: string }) {
    const { name, logo } = useBrand();

    return (
        <div className={cn('flex items-center gap-2', className)}>
            <img src={logo} alt="" width={32} height={32} className="size-8" />
            <span className="text-base font-semibold">{name}</span>
        </div>
    );
}
