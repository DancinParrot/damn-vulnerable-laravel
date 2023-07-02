import FileInput from "@/Components/FileInput";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import { Transition } from "@headlessui/react";
import { router, useForm } from "@inertiajs/react";
import { useRef, FormEventHandler } from 'react';

interface FormProps {
    file_input: File | undefined;
}

export default function UpdateProfileAvatart({ className = '' }: { className?: string }) {
    const fileInput = useRef<HTMLInputElement>();

    const { data, setData, errors, post, reset, processing, recentlySuccessful } = useForm<FormProps>({
        file_input: undefined,
    });

    const updateAvatar: FormEventHandler = (e) => {
        e.preventDefault();

        /* router.post(`/upload-avatar`, {
            _method: 'put',
            file: data.file_input,
        }) */

        post(route('avatar.upload'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errors) => {
                if (errors.file_input) {
                    reset('file_input');
                    fileInput.current?.focus();
                }
            },
        });
    };

    const handleFile = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            setData("file_input", e.target.files[0]);
            console.log(data.file_input, " file_input");
        }
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900">Update Password</h2>

                <p className="mt-1 text-sm text-gray-600">
                    Ensure your account is using a long, random password to stay secure.
                </p>
            </header>

            <div>

                <form onSubmit={updateAvatar} className="mt-6 space-y-6">
                    <div>

                        <InputLabel htmlFor="file_input" value="File" />

                        <FileInput
                            id="file_input"
                            ref={fileInput}
                            onChange={handleFile}
                            type="file"
                            className="mt-1 block w-full"
                        />

                        <InputError message={errors.file_input} className="mt-2" />
                    </div>

                    <div className="flex items-center gap-4">
                        <PrimaryButton disabled={processing}>Save</PrimaryButton>

                        <Transition
                            show={recentlySuccessful}
                            enterFrom="opacity-0"
                            leaveTo="opacity-0"
                            className="transition ease-in-out"
                        >
                            <p className="text-sm text-gray-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
                <div>
                    {/*TODO: Display image*/}
                </div>
            </div>

        </section>
    )
}
