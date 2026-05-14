import React, { useMemo } from 'react';
import { FormControl, FormHelperText, InputLabel, OutlinedInput } from '@mui/material';

interface DateFieldProps {
    value: string | Date | null | undefined;
    onChange: (value: string) => void;
    formatDate: (date: string | Date | null | undefined) => string;
    label?: string;
    helperText?: string;
    error?: boolean;
    errorText?: string;
    required?: boolean;
    fullWidth?: boolean;
    disabled?: boolean;
    size?: 'small' | 'medium';
    maxDate?: string | Date | (() => string | Date);
    minDate?: string | Date | (() => string | Date);
    id?: string;
    sx?: React.CSSProperties;
}

export const DateField: React.FC<DateFieldProps> = ({
    value,
    onChange,
    formatDate,
    label = 'Data',
    helperText,
    error = false,
    errorText,
    required = false,
    fullWidth = true,
    disabled = false,
    size = 'medium',
    maxDate,
    minDate,
    id,
    sx,
}) => {
    const inputId = id || `date-field-${Math.random().toString(36).slice(2, 11)}`;
    const formattedValue = formatDate(value) || '';
    const hasValue = Boolean(value && value !== '');

    const maxDateStr = useMemo(() => {
        if (!maxDate) return undefined;
        if (typeof maxDate === 'function') {
            const result = maxDate();
            return result instanceof Date ? result.toISOString().split('T')[0] : result;
        }
        return maxDate instanceof Date ? maxDate.toISOString().split('T')[0] : maxDate;
    }, [maxDate]);

    const minDateStr = useMemo(() => {
        if (!minDate) return undefined;
        if (typeof minDate === 'function') {
            const result = minDate();
            return result instanceof Date ? result.toISOString().split('T')[0] : result;
        }
        return minDate instanceof Date ? minDate.toISOString().split('T')[0] : minDate;
    }, [minDate]);

    const displayHelperText = error && errorText ? errorText : helperText;

    return (
        <FormControl
            fullWidth={fullWidth}
            variant="outlined"
            error={error}
            required={required}
            disabled={disabled}
            size={size}
        >
            <InputLabel shrink htmlFor={inputId}>
                {label}
            </InputLabel>
            <OutlinedInput
                id={inputId}
                type="date"
                value={formattedValue}
                onChange={(e) => onChange(e.target.value)}
                label={label}
                notched
                disabled={disabled}
                inputProps={{
                    max: maxDateStr,
                    min: minDateStr,
                }}
                sx={{
                    ...sx,
                    ...(!hasValue && !disabled && {
                        '& input[type="date"]': {
                            color: 'transparent',
                            '&::-webkit-datetime-edit': { color: 'transparent' },
                            '&::-webkit-datetime-edit-fields-wrapper': { color: 'transparent' },
                            '&::-webkit-calendar-picker-indicator': { opacity: 0.6 },
                        },
                    }),
                }}
            />
            {displayHelperText && (
                <FormHelperText error={error}>
                    {displayHelperText}
                </FormHelperText>
            )}
        </FormControl>
    );
};