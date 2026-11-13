import { MoreHorizontal } from 'lucide-react';
import { Fragment } from 'react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { DropdownContent, DropdownItem, DropdownLabel, DropdownMenu, DropdownSeparator, DropdownTrigger } from '@/components/ui/dropdown';
import { Tip } from '@/components/ui/tooltip';

export type RowAction = {
    label: string;
    icon: ReactNode;
    onSelect: () => void;
    /** Marks a destructive action; it goes last, below a separator. */
    danger?: boolean;
    disabled?: boolean;
    /** Why a disabled action is not available, shown when the pointer rests on it. */
    reason?: string;
};

/** The actions of one table row behind a single "more" button. `title` names the button for screen readers; `heading` also shows it at the top of the menu. */
export function RowActions({ title, heading, actions }: { title: string; heading?: string; actions: RowAction[] }) {
    return (
        <DropdownMenu>
            <Tip label="Aksi">
                <DropdownTrigger asChild>
                    <Button variant="ghost" size="icon" aria-label={`Aksi ${title}`}>
                        <MoreHorizontal />
                    </Button>
                </DropdownTrigger>
            </Tip>
            <DropdownContent>
                {heading !== undefined && <DropdownLabel>{heading}</DropdownLabel>}
                {actions.map((action, index) => (
                    <Fragment key={action.label}>
                        {action.danger === true && index > 0 && actions[index - 1]?.danger !== true && <DropdownSeparator />}
                        <DropdownItem
                            danger={action.danger}
                            icon={action.icon}
                            disabled={action.disabled}
                            title={action.disabled ? action.reason : undefined}
                            onSelect={action.onSelect}
                        >
                            {action.label}
                        </DropdownItem>
                    </Fragment>
                ))}
            </DropdownContent>
        </DropdownMenu>
    );
}
