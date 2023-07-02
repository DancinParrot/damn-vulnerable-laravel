import { forwardRef, useEffect, useImperativeHandle, useRef, InputHTMLAttributes } from 'react';

interface Props extends React.InputHTMLAttributes<HTMLInputElement> {
    value?: any;
    onChange: React.ChangeEventHandler<HTMLInputElement>;
}

export default forwardRef(function FileInput(
    { type = 'file', className = '', isFocused = false, value, onChange, ...props }: Props & { isFocused?: boolean },
    ref
) {
    const localRef = useRef<HTMLInputElement>(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, []);

    return (
        <input
            {...props}
            value={value}
            type={type}
            onChange={(e) => onChange(e)}
            className={
                'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm ' +
                className
            }
            ref={localRef}
        />
    );
});
