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
        CheckCircle2,
        Circle,
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
        { label: 'Harga', icon: Clock3 },
        { label: 'Jadwal', icon: Clock3 },
        { label: 'Armada', icon: Bus },
        { label: 'Driver', icon: UserRound },
    ];
    const dayOptions = [
        { value: 1, label: 'Sen' },
        { value: 2, label: 'Sel' },
        { value: 3, label: 'Rab' },
        { value: 4, label: 'Kam' },
        { value: 5, label: 'Jum' },
        { value: 6, label: 'Sab' },
        { value: 0, label: 'Min' },
    ];

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
        pickupTimes = [...pickupTimes, ''];
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
</script>

<AppHead title="Lengkapi Data Travel" />

<Form
    action={formAction}
    method="post"
    class="overflow-hidden rounded-[1.4rem] border border-[#d7dfd5] bg-white/94 shadow-[0_26px_80px_-46px_rgba(16,61,58,0.85)] backdrop-blur"
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

        <div class="grid gap-4 p-4 sm:p-5">
            <div class="space-y-1">
                <p
                    class="text-xs font-semibold uppercase tracking-[0.18em] text-[#7b6a43]"
                >
                    {continuationMode ? 'Lanjutkan setup' : 'Setup awal'}
                </p>
                <h2
                    class="text-xl font-semibold tracking-[-0.03em] text-[#103d3a]"
                >
                    Buat aplikasi siap dipakai.
                </h2>
            </div>

            {#if continuationMode && progressItems.length > 0}
                <section
                    class="rounded-2xl border border-[#d9ded4]/90 bg-[#fbfcf8] p-3"
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
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
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

            <nav class="grid grid-cols-5 gap-1 rounded-2xl bg-[#eef1e8] p-1">
                {#each steps as step, index}
                    {@const Icon = step.icon}
                    <button
                        type="button"
                        class={`min-w-0 rounded-xl px-2 py-2 text-center text-[11px] font-semibold transition ${
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
                        <Icon class="mx-auto mb-1 h-3.5 w-3.5" />
                        <span class="block truncate"
                            >{index + 1}. {step.label}</span
                        >
                    </button>
                {/each}
            </nav>

            {#if localError}
                <div
                    class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
                >
                    {localError}
                </div>
            {/if}

            <section
                class="grid gap-4 rounded-2xl border border-[#d9ded4]/90 bg-[#fbfcf8] p-3 sm:p-4"
            >
                {#if currentStep === 0}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
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
                        <div class="grid gap-2">
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
                        <div class="grid gap-2">
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
                        <div class="grid gap-2">
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
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="segment_origin_view">Titik awal</Label>
                            <Input
                                id="segment_origin_view"
                                bind:value={segmentOrigin}
                                placeholder={origin || 'Pool Pinrang'}
                            />
                            <InputError message={errors.segment_origin} />
                        </div>
                        <div class="grid gap-2">
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
                        <div class="grid gap-2">
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
                        <div class="grid gap-2">
                            <Label>Jam pickup</Label>
                            <div class="grid gap-2">
                                {#each pickupTimes as time, index}
                                    <div class="flex gap-2">
                                        <Input
                                            type="time"
                                            bind:value={pickupTimes[index]}
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            class="shrink-0"
                                            onclick={() =>
                                                removePickupTime(index)}
                                            >Hapus</Button
                                        >
                                    </div>
                                {/each}
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="w-fit"
                                    onclick={addPickupTime}>Tambah jam</Button
                                >
                            </div>
                            <InputError message={errors.pickup_times} />
                        </div>
                    </div>
                {:else if currentStep === 2}
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label>Hari operasi</Label>
                            <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                                {#each dayOptions as day}
                                    <label
                                        class={`cursor-pointer rounded-xl border px-3 py-2 text-center text-xs font-semibold transition ${
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
                                        {day.label}
                                    </label>
                                {/each}
                            </div>
                            <InputError message={errors.schedule_days} />
                        </div>
                        <div class="grid gap-2 sm:max-w-xs">
                            <Label for="departure_time_view"
                                >Jam berangkat</Label
                            >
                            <Input
                                id="departure_time_view"
                                type="time"
                                bind:value={departureTime}
                            />
                            <InputError message={errors.departure_time} />
                        </div>
                    </div>
                {:else if currentStep === 3}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
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
                        <div class="grid gap-2">
                            <Label for="unit_category_view">Kategori</Label>
                            <select
                                id="unit_category_view"
                                bind:value={unitCategory}
                                class="h-10 rounded-md border border-input bg-card px-3 text-sm shadow-xs"
                            >
                                <option value="">Pilih kategori</option>
                                <option value="Minibus">Minibus</option>
                                <option value="Mediumbus">Mediumbus</option>
                                <option value="Bigbus">Bigbus</option>
                            </select>
                            <InputError message={errors.unit_category} />
                        </div>
                        <div class="grid gap-2">
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
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="unit_nopol_view">Nopol armada</Label>
                            <Input
                                id="unit_nopol_view"
                                bind:value={unitNopol}
                                placeholder="DD 1234 XX"
                            />
                            <InputError message={errors.unit_nopol} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="armada_merk_view">Merk</Label>
                            <Input
                                id="armada_merk_view"
                                bind:value={armadaMerk}
                                placeholder="Toyota / Mitsubishi"
                            />
                            <InputError message={errors.armada_merk} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="driver_name_view">Nama driver</Label>
                            <Input
                                id="driver_name_view"
                                bind:value={driverName}
                                placeholder="Nama driver"
                            />
                            <InputError message={errors.driver_name} />
                        </div>
                        <div class="grid gap-2">
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
                            class="rounded-xl"
                            onclick={previousStep}
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" /> Kembali
                        </Button>
                    {/if}
                    {#if currentStep < steps.length - 1}
                        <Button
                            type="submit"
                            variant="outline"
                            class="rounded-xl"
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
                        class="h-11 rounded-xl bg-[#103d3a] text-white hover:bg-[#0b2f2c]"
                        onclick={nextStep}
                        disabled={processing}
                    >
                        Simpan & Lanjut
                        <ArrowRight class="ml-2 h-4 w-4" />
                    </Button>
                {:else}
                    <Button
                        type="submit"
                        class="h-11 rounded-xl bg-[#103d3a] text-white hover:bg-[#0b2f2c]"
                        disabled={processing}
                    >
                        {#if processing}<Spinner />{/if}
                        Simpan dan Buka Dasbor
                        {#if !processing}<ArrowRight
                                class="ml-2 h-4 w-4"
                            />{/if}
                    </Button>
                {/if}
            </div>
        </div>
    {/snippet}
</Form>
