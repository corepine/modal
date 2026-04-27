<?php

namespace Corepine\Modal\Support;

final class ModalActionClasses
{
    public const BASE = 'inline-flex min-h-10 items-center justify-center gap-2 rounded-md border px-3.5 py-2 text-sm font-medium leading-5 transition-[background-color,border-color,color,box-shadow,opacity] duration-150 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/80 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-900/20';

    public const DISABLED = 'pointer-events-none cursor-not-allowed opacity-[0.55]';
}
