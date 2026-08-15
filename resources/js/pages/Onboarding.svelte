<script module lang="ts">
    export const layout = {
        title: 'Lengkapi Data Travel',
        description: '',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import {
        ArrowLeft,
        ArrowRight,
        Bus,
        CalendarDays,
        CheckCircle2,
        Circle,
        CircleDollarSign,
        Clock3,
        UserRound,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';

    type SetupProgress = {
        completed?: boolean;
        completed_count?: number;
        total_count?: number;
        percent?: number;
        items?: Array<{ key: string; label: string; done: boolean }>;
    };

    type Defaults = {
        travel_name?: string;
        phone?: string;
        origin?: string;
        destination?: string;
    };

    let {
        selectedPlan = 'starter',
        registrationIntent = 'trial',
        continuationMode = false,
        setupProgress = null,
        defaults = {},
    }: {
        selectedPlan?: string;
        registrationIntent?: 'trial' | 'paid' | 'payment';
        continuationMode?: boolean;
        setupProgress?: SetupProgress | null;
        defaults?: Defaults;
    } = $props();

    const formAction = '/onboarding';
    const steps = [
        { label: 'Travel', icon: Bus },
        { label: 'Jadwal', icon: CalendarDays },
        { label: 'Harga', icon: CircleDollarSign },
        { label: 'Armada', icon: Bus },
        { label: 'Driver', icon: UserRound },
    ];
    const dayOptions = [
        { value: 1, shortLabel: 'Sen', label: 'Senin' },
        { value: 2, shortLabel: 'Sel', label: 'Selasa' },
        { value: 3, shortLabel: 'Rab', label: 'Rabu' },
        { value: 4, shortLabel: 'Kam', label: 'Kamis' },
        { value: 5, shortLabel: 'Jum', label: 'Jumat' },
        { value: 6, shortLabel: 'Sab', label: 'Sabtu' },
        { value: 0, shortLabel: 'Min', label: 'Minggu' },
    ];
    const timeSuggestions = ['06:00', '08:00', '09:00', '12:00', '15:00', '18:00'];

    let currentStep = $state(0);
    let localError = $state('');
    let travelName = $state(untrack(() => defaults.travel_name ?? ''));
    let phone = $state(untrack(() => defaults.phone ?? ''));
    let origin = $state(untrack(() => defaults.origin ?? ''));
    let destination = $state(untrack(() => defaults.destination ?? ''));
    let segmentOrigin = $state('');
    let segmentDestination = $state('');
    let pickupTimes = $state(['']);
    let ticketPrice = $state('');
    let scheduleDays = $state<number[]>([]);
    let departureTime = $state('');
    let unitTemplateName = $state('');
    let unitCategory = $state('');
    let seatCapacity = $state('');
    let unitNopol = $state('');
    let armadaMerk = $state('');
    let driverName = $state('');
    let driverPhone = $state('');

    const progressItems = $derived(setupProgress?.items ?? []);
    const progressPercent = $derived(Number(setupProgress?.percent ?? 0));
    const selectedDayLabels = $derived(
        dayOptions
            .filter((day) => scheduleDays.includes(day.value))
            .map((day) => day.label),
    );

    function normalizedIntent(): 'trial' | 'paid' {
        return registrationIntent === 'payment' ? 'paid' : registrationIntent;
    }

    function canLeaveTravelStep(): boolean {
        return (
            travelName.trim() !== '' &&
            phone.trim() !== '' &&
            origin.trim() !== '' &&
            destination.trim() !== ''
        );
    }

    function nextStep(): void {
        localError = '';
        if (currentStep === 0 && !canLeaveTravelStep()) {
            localError = 'Lengkapi data travel dan rute utama dulu.';
            return;
        }
        currentStep = Math.min(currentStep + 1, steps.length - 1);
    }

    function previousStep(): void {
        localError = '';
        currentStep = Math.max(currentStep - 1, 0);
    }

    function addPickupTime(): void {
        pickupTimes = [...filledPickupTimes(), ''];
    }

    function chooseDepartureTime(time: string): void {
        departureTime = time;
    }

    function togglePickupTime(time: string): void {
        const current = filledPickupTimes();
        pickupTimes = current.includes(time)
            ? current.filter((item) => item !== time)
            : [...current, time];

        if (pickupTimes.length === 0) {
            pickupTimes = [''];
        }
    }

    function removePickupTime(index: number): void {
        pickupTimes = pickupTimes.filter((_, itemIndex) => itemIndex !== index);
        if (pickupTimes.length === 0) {
            pickupTimes = [''];
        }
    }

    function filledPickupTimes(): string[] {
        return pickupTimes.map((item) => item.trim()).filter(Boolean);
    }

    function firstFormError(errors: Record<string, unknown>): string {
        for (const value of Object.values(errors)) {
            if (Array.isArray(value) && value.length > 0) {
                return String(value[0] ?? '').trim();
            }

            if (typeof value === 'string' && value.trim() !== '') {
                return value.trim();
            }
        }

        return '';
    }
</script>

<AppHead title="Lengkapi Data Travel" />

<Form
    action={formAction}
    method="post"
    class="overflow-hidden rounded-[1.1rem] border border-[#d7dfd5] bg-white/94 text-[13px] shadow-[0_20px_60px_-42px_rgba(16,61,58,0.85)] backdrop-blur md:[&_input]:h-8 md:[&_input]:px-2.5 md:[&_input]:text-[13px] md:[&_label]:text-xs"
>
    {#snippet children({ errors, processing })}
        <input type="hidden" name="plan" value={selectedPlan} />
        <input
            type="hidden"
            name="registration_intent"
            value={normalizedIntent()}
        />
        <input type="hidden" name="billing_interval" value="monthly" />
        <input type="hidden" name="travel_name" value={travelName} />
        <input type="hidden" name="phone" value={phone} />
        <input type="hidden" name="origin" value={origin} />
        <input type="hidden" name="destination" value={destination} />
        <input type="hidden" name="segment_origin" value={segmentOrigin} />
        <input
            type="hidden"
            name="segment_destination"
            value={segmentDestination}
        />
        <input type="hidden" name="ticket_price" value={ticketPrice} />
        <input type="hidden" name="departure_time" value={departureTime} />
        <input
            type="hidden"
            name="unit_template_name"
            value={unitTemplateName}
        />
        <input type="hidden" name="unit_category" value={unitCategory} />
        <input type="hidden" name="seat_capacity" value={seatCapacity} />
        <input type="hidden" name="unit_nopol" value={unitNopol} />
        <input type="hidden" name="armada_merk" value={armadaMerk} />
        <input type="hidden" name="driver_name" value={driverName} />
        <input type="hidden" name="driver_phone" value={driverPhone} />
        {#each filledPickupTimes() as time}
            <input type="hidden" name="pickup_times[]" value={time} />
        {/each}
        {#each scheduleDays as day}
            <input type="hidden" name="schedule_days[]" value={day} />
        {/each}

        <div class="grid gap-3 p-3 sm:p-4">
            <div class="space-y-1">
                <p
                    class="text-xs font-semibold uppercase tracking-[0.18em] text-[#7b6a43]"
                >
                    {continuationMode ? 'Lanjutkan setup' : 'Setup awal'}
                </p>
                <h2
                    class="text-lg font-semibold tracking-[-0.03em] text-[#103d3a]"
                >
                    Buat aplikasi siap dipakai.
                </h2>
            </div>

            {#if continuationMode && progressItems.length > 0}
                <section
                    class="rounded-xl border border-[#d9ded4]/90 bg-[#fbfcf8] p-3"
                >
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-semibold text-[#103d3a]"
                            >Progress</span
                        >
                        <span class="text-xs font-semibold text-[#7b6a43]"
                            >{progressPercent}%</span
                        >
                    </div>
                    <div
                        class="mt-2 h-2 overflow-hidden rounded-full bg-[#e8eadf]"
                    >
                        <div
                            class="h-full rounded-full bg-[#0d7066]"
                            style={`width:${progressPercent}%`}
                        ></div>
                    </div>
                    <div class="mt-2 grid gap-1.5 sm:grid-cols-2">
                        {#each progressItems as item}
                            <div class="flex items-center gap-2 text-xs">
                                {#if item.done}
                                    <CheckCircle2
                                        class="h-4 w-4 text-emerald-600"
                                    />
                                {:else}
                                    <Circle class="h-4 w-4 text-[#b96c20]" />
                                {/if}
                                <span
                                    class={item.done
                                        ? 'text-[#103d3a]'
                                        : 'text-[#7b6a43]'}
                                >
                                    {item.label}
                                </span>
                            </div>
                        {/each}
                    </div>
                </section>
            {/if}

            <nav class="grid grid-cols-5 gap-1 rounded-xl bg-[#eef1e8] p-1">
                {#each steps as step, index}
                    {@const Icon = step.icon}
                    <button
                        type="button"
                        class={`min-w-0 rounded-lg px-1.5 py-1.5 text-center text-[10px] font-semibold transition ${
                            currentStep === index
                                ? 'bg-white text-[#103d3a] shadow-sm'
                                : 'text-[#67726c] hover:bg-white/60'
                        }`}
                        onclick={() => {
                            if (index === 0 || canLeaveTravelStep()) {
                                localError = '';
                                currentStep = index;
                            } else {
                                localError =
                                    'Lengkapi data travel dan rute utama dulu.';
                            }
                        }}
                    >
                        <Icon class="mx-auto mb-1 h-3 w-3" />
                        <span class="block truncate"
                            >{index + 1}. {step.label}</span
                        >
                    </button>
                {/each}
            </nav>

            {#if localError}
                <div
                    class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800"
                >
                    {localError}
                </div>
            {/if}
            {#if firstFormError(errors)}
                <div
                    role="alert"
                    class="rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-xs leading-relaxed text-red-800"
                >
                    <p class="font-semibold">Data belum dapat disimpan</p>
                    <p class="mt-0.5">{firstFormError(errors)}</p>
                </div>
            {/if}

            <section
                class="grid gap-3 rounded-xl border border-[#d9ded4]/90 bg-[#fbfcf8] p-3"
            >
                {#if currentStep === 0}
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="travel_name_view"
                                >Nama Travel / Pool</Label
                            >
                            <Input
                                id="travel_name_view"
                                type="text"
                                required
                                bind:value={travelName}
                                placeholder="Mandiri Trans"
                            />
                            <InputError message={errors.travel_name} />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="phone_view">Nomor WhatsApp</Label>
                            <Input
                                id="phone_view"
                                type="tel"
                                required
                                bind:value={phone}
                                placeholder="0852xxxx"
                            />
                            <InputError message={errors.phone} />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="origin_view">Dari</Label>
                            <Input
                                id="origin_view"
                                type="text"
                                required
                                bind:value={origin}
                                placeholder="Pinrang"
                            />
                            <InputError message={errors.origin} />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="destination_view">Tujuan</Label>
                            <Input
                                id="destination_view"
                                type="text"
                                required
                                bind:value={destination}
                                placeholder="Makassar"
                            />
                            <InputError message={errors.destination} />
                        </div>
                    </div>
                {:else if currentStep === 1}
                    <div class="grid gap-3">
                        <div class="rounded-xl border border-[#cfe3d4] bg-[#eef8f0] p-3">
                            <div class="flex items-start gap-2.5">
                                <div class="rounded-lg bg-white p-2 text-[#0d7066] shadow-sm">
                                    <CalendarDays class="h-4 w-4" />
                                </div>
                                <div>
                                    <p class="font-semibold text-[#103d3a]">
                                        Kapan kendaraan berangkat?
                                    </p>
                                    <p class="mt-0.5 text-xs leading-relaxed text-[#587066]">
                                        Pilih hari operasi dan satu jam keberangkatan utama.
                                        Jam bisa diubah lagi nanti.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-1.5">
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <Label>Hari keberangkatan</Label>
                                    <p class="mt-1 text-[11px] text-[#718079]">
                                        Pilih satu atau beberapa hari dalam seminggu.
                                    </p>
                                </div>
                                {#if scheduleDays.length > 0}
                                    <span class="shrink-0 text-[11px] font-semibold text-[#0d7066]">
                                        {scheduleDays.length} hari dipilih
                                    </span>
                                {/if}
                            </div>
                            <div class="grid grid-cols-4 gap-1.5 sm:grid-cols-7">
                                {#each dayOptions as day}
                                    <label
                                        title={day.label}
                                        class={`cursor-pointer rounded-lg border px-2 py-1.5 text-center text-xs font-semibold transition ${
                                            scheduleDays.includes(day.value)
                                                ? 'border-[#0d7066] bg-emerald-50 text-[#103d3a]'
                                                : 'border-[#d9ded4] bg-white text-[#67726c]'
                                        }`}
                                    >
                                        <input
                                            class="sr-only"
                                            type="checkbox"
                                            bind:group={scheduleDays}
                                            value={day.value}
                                        />
                                        {day.shortLabel}
                                    </label>
                                {/each}
                            </div>
                            <InputError message={errors.schedule_days} />
                        </div>

                        <div class="grid gap-2 rounded-xl border border-[#d9ded4] bg-white p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <Label for="departure_time_view">Jam berangkat</Label>
                                    <p class="mt-1 text-[11px] text-[#718079]">
                                        Pilih waktu yang umum atau isi sendiri.
                                    </p>
                                </div>
                                {#if departureTime}
                                    <span class="rounded-full bg-[#103d3a] px-2.5 py-1 text-xs font-bold text-white">
                                        {departureTime}
                                    </span>
                                {/if}
                            </div>
                            <div class="flex flex-wrap gap-1.5" aria-label="Pilihan cepat jam berangkat">
                                {#each timeSuggestions as time}
                                    <button
                                        type="button"
                                        class={`rounded-full border px-3 py-1.5 text-xs font-semibold transition ${
                                            departureTime === time
                                                ? 'border-[#103d3a] bg-[#103d3a] text-white shadow-sm'
                                                : 'border-[#d9ded4] bg-[#fbfcf8] text-[#50645c] hover:border-[#0d7066] hover:text-[#0d7066]'
                                        }`}
                                        aria-pressed={departureTime === time}
                                        onclick={() => chooseDepartureTime(time)}
                                    >
                                        {time}
                                    </button>
                                {/each}
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-px flex-1 bg-[#e6ebe3]"></div>
                                <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-[#9aa69f]">atau isi manual</span>
                                <div class="h-px flex-1 bg-[#e6ebe3]"></div>
                            </div>
                            <Input
                                id="departure_time_view"
                                type="time"
                                bind:value={departureTime}
                                class="h-11 text-base font-semibold tracking-wide sm:max-w-xs"
                                aria-describedby="departure_time_help"
                            />
                            <p id="departure_time_help" class="text-[11px] leading-relaxed text-[#718079]">
                                Format 24 jam. Contoh: 09:30 berarti berangkat pukul setengah sepuluh pagi.
                            </p>
                            <InputError message={errors.departure_time} />
                        </div>

                        <div class="rounded-xl border border-dashed border-[#cbd8cc] bg-[#fbfcf8] px-3 py-2.5 text-xs text-[#587066]">
                            <span class="font-semibold text-[#103d3a]">Ringkasan:</span>
                            {#if selectedDayLabels.length > 0 || departureTime}
                                {selectedDayLabels.length > 0 ? selectedDayLabels.join(', ') : 'hari belum dipilih'}
                                {departureTime ? ` pukul ${departureTime}` : ', jam belum dipilih'}.
                            {:else}
                                Hari dan jam belum dipilih. Anda dapat melewati langkah ini dan mengaturnya nanti.
                            {/if}
                        </div>
                    </div>
                {:else if currentStep === 2}
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="segment_origin_view">Titik awal</Label>
                            <Input
                                id="segment_origin_view"
                                bind:value={segmentOrigin}
                                placeholder={origin || 'Pool Pinrang'}
                            />
                            <InputError message={errors.segment_origin} />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="segment_destination_view"
                                >Titik akhir</Label
                            >
                            <Input
                                id="segment_destination_view"
                                bind:value={segmentDestination}
                                placeholder={destination || 'Makassar'}
                            />
                            <InputError message={errors.segment_destination} />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="ticket_price_view">Harga tiket</Label>
                            <Input
                                id="ticket_price_view"
                                type="number"
                                min="0"
                                bind:value={ticketPrice}
                                placeholder="120000"
                            />
                            <InputError message={errors.ticket_price} />
                        </div>
                        <div class="grid gap-1.5">
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <Label>Jam pickup</Label>
                                    <p class="mt-1 text-[11px] text-[#718079]">
                                        Opsional. Pilih semua jam layanan yang tersedia.
                                    </p>
                                </div>
                                {#if filledPickupTimes().length > 0}
                                    <span class="shrink-0 text-[11px] font-semibold text-[#0d7066]">
                                        {filledPickupTimes().length} jam
                                    </span>
                                {/if}
                            </div>
                            <div class="flex flex-wrap gap-1.5" aria-label="Pilihan cepat jam pickup">
                                {#each timeSuggestions as time}
                                    <button
                                        type="button"
                                        class={`rounded-full border px-2.5 py-1.5 text-xs font-semibold transition ${
                                            filledPickupTimes().includes(time)
                                                ? 'border-[#0d7066] bg-emerald-50 text-[#103d3a]'
                                                : 'border-[#d9ded4] bg-[#fbfcf8] text-[#50645c] hover:border-[#0d7066] hover:text-[#0d7066]'
                                        }`}
                                        aria-pressed={filledPickupTimes().includes(time)}
                                        onclick={() => togglePickupTime(time)}
                                    >
                                        {time}
                                    </button>
                                {/each}
                            </div>
                            <div class="grid gap-1.5">
                                {#each pickupTimes as time, index}
                                    <div class="flex items-center gap-2">
                                        <span class="w-16 shrink-0 text-[11px] font-semibold text-[#718079]">
                                            Pickup {index + 1}
                                        </span>
                                        <Input
                                            type="time"
                                            bind:value={pickupTimes[index]}
                                            aria-label={`Jam pickup ${index + 1}`}
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            class="h-9 shrink-0 px-2.5 text-xs"
                                            aria-label={`Hapus jam pickup ${index + 1}`}
                                            onclick={() =>
                                                removePickupTime(index)}
                                            >Hapus</Button
                                        >
                                    </div>
                                {/each}
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="w-fit rounded-lg text-xs"
                                    onclick={addPickupTime}
                                    >+ Tambah jam manual</Button
                                >
                            </div>
                            <InputError message={errors.pickup_times} />
                        </div>
                    </div>
                {:else if currentStep === 3}
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="unit_template_name_view"
                                >Nama kategori</Label
                            >
                            <Input
                                id="unit_template_name_view"
                                bind:value={unitTemplateName}
                                placeholder="Minibus 8 Seat"
                            />
                            <InputError message={errors.unit_template_name} />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="unit_category_view">Kategori</Label>
                            <select
                                id="unit_category_view"
                                bind:value={unitCategory}
                                class="h-8 rounded-md border border-input bg-card px-2.5 text-[13px] shadow-xs"
                            >
                                <option value="">Pilih kategori</option>
                                <option value="Minibus">Minibus</option>
                                <option value="Mediumbus">Mediumbus</option>
                                <option value="Bigbus">Bigbus</option>
                            </select>
                            <InputError message={errors.unit_category} />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="seat_capacity_view">Jumlah kursi</Label>
                            <Input
                                id="seat_capacity_view"
                                type="number"
                                min="0"
                                bind:value={seatCapacity}
                                placeholder="8"
                            />
                            <InputError message={errors.seat_capacity} />
                        </div>
                    </div>
                {:else}
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="unit_nopol_view">Nopol armada</Label>
                            <Input
                                id="unit_nopol_view"
                                bind:value={unitNopol}
                                placeholder="DD 1234 XX"
                            />
                            <InputError message={errors.unit_nopol} />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="armada_merk_view">Merk</Label>
                            <Input
                                id="armada_merk_view"
                                bind:value={armadaMerk}
                                placeholder="Toyota / Mitsubishi"
                            />
                            <InputError message={errors.armada_merk} />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="driver_name_view">Nama driver</Label>
                            <Input
                                id="driver_name_view"
                                bind:value={driverName}
                                placeholder="Nama driver"
                            />
                            <InputError message={errors.driver_name} />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="driver_phone_view"
                                >WhatsApp driver</Label
                            >
                            <Input
                                id="driver_phone_view"
                                type="tel"
                                bind:value={driverPhone}
                                placeholder="0852xxxx"
                            />
                            <InputError message={errors.driver_phone} />
                        </div>
                    </div>
                {/if}
            </section>

            <div
                class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex gap-2">
                    {#if currentStep > 0}
                        <Button
                            type="button"
                            variant="outline"
                            class="h-9 rounded-lg text-[13px]"
                            onclick={previousStep}
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" /> Kembali
                        </Button>
                    {/if}
                    {#if currentStep < steps.length - 1}
                        <Button
                            type="submit"
                            variant="outline"
                            class="h-9 rounded-lg text-[13px]"
                            disabled={processing ||
                                (currentStep === 0 && !canLeaveTravelStep())}
                        >
                            Lewati dulu
                        </Button>
                    {/if}
                </div>

                {#if currentStep < steps.length - 1}
                    <Button
                        type="button"
                        class="h-9 rounded-lg bg-[#103d3a] text-[13px] text-white hover:bg-[#0b2f2c]"
                        onclick={nextStep}
                        disabled={processing}
                    >
                        Simpan & Lanjut
                        <ArrowRight class="ml-2 h-4 w-4" />
                    </Button>
                {:else}
                    <Button
                        type="submit"
                        class="h-9 rounded-lg bg-[#103d3a] text-[13px] text-white hover:bg-[#0b2f2c]"
                        disabled={processing}
                        aria-busy={processing}
                    >
                        {#if processing}
                            <Spinner />
                            Menyimpan...
                        {:else}
                            Simpan dan Buka Dasbor
                        {/if}
                        {#if !processing}<ArrowRight
                                class="ml-2 h-4 w-4"
                            />{/if}
                    </Button>
                {/if}
            </div>
        </div>
    {/snippet}
</Form>
