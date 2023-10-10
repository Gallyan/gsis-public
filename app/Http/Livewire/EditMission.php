<?php

namespace App\Http\Livewire;

use App\Mail\MissionStatusChange;
use App\Models\Document;
use App\Models\Institution;
use App\Models\Manager;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditMission extends Component
{
    use WithFileUploads, AuthorizesRequests;

    public Mission $mission;

    public $uploads = [];

    public $programme; // Store uploaded conference programme temporary upload

    public $del_docs = [];

    public $modified = false; // True if form is modified and need to be saved

    public $disabled = false; // True if user can't modify current editing Mission

    public $disabledStatuses = []; // List of disabled status

    public $showInformationMessage = false;

    public $showTicket = false;

    public $showHotel = false;

    public $isAuthManager = false;

    public $showWP = false;

    // Ticket
    public $ticket_id;

    public $ticket_mode;

    public $ticket_direction;

    public $ticket_number;

    public $ticket_date;

    public $ticket_time;

    public $ticket_from;

    public $ticket_to;

    // Hotel
    public $hotel_id;

    public $hotel_name;

    public $hotel_city;

    public $hotel_start;

    public $hotel_end;

    protected function rules()
    {
        return [
            'mission.user_id' => 'required|exists:users,id',
            'mission.subject' => 'required|string|max:255',
            'mission.institution_id' => 'required|exists:institutions,id',
            'mission.om' => 'required_if:mission.status,processed',
            'mission.wp' => [
                'sometimes',
                Rule::requiredIf(fn () => Institution::find($this->mission->institution_id) &&
                                          Institution::find($this->mission->institution_id)->wp),
                'nullable',
                'integer',
                'min:1',
            ],
            'mission.conference' => 'boolean',
            'mission.conf_amount' => 'nullable|float',
            'mission.conf_currency' => 'required_with:conf_amount|nullable|string|size:3',
            'mission.costs' => 'boolean',
            'mission.dest_country' => 'string|max:2|uppercase',
            'mission.dest_city' => 'string|max:50',
            'mission.departure' => 'required|date',
            'mission.from' => 'string|in:home,work',
            'mission.return' => 'required|date|after_or_equal:mission.departure',
            'mission.to' => 'string|in:home,work',
            'mission.tickets' => 'nullable|array',
            'mission.hotels' => 'nullable|array',
            'mission.meal' => 'nullable|string|in:forfait,reel',
            'mission.taxi' => 'boolean',
            'mission.transport' => 'boolean',
            'mission.personal_car' => 'boolean',
            'mission.rental_car' => 'boolean',
            'mission.parking' => 'boolean',
            'mission.registration' => 'nullable|boolean',
            'mission.accomodation' => 'nullable|boolean',
            'mission.others' => 'nullable|string',
            'programme' => 'nullable|mimes:xls,xlsx,doc,docx,pdf,zip,jpg,png,gif,bmp,webp,svg|max:10240',
            'uploads' => 'nullable|array',
            'uploads.*' => 'mimes:xls,xlsx,doc,docx,pdf,zip,jpg,png,gif,bmp,webp,svg|max:10240',
            'mission.comments' => 'nullable|string',
            'mission.status' => 'required|in:'.collect(Mission::STATUSES)->keys()->implode(','),
        ];
    }

    protected function ticket_rules()
    {
        return [
            'ticket_mode' => 'required|string',
            'ticket_direction' => 'required|boolean',
            'ticket_number' => 'nullable|string',
            'ticket_date' => 'required|date',
            'ticket_time' => 'required|date_format:H:i',
            'ticket_from' => 'required|string',
            'ticket_to' => 'required|string',
        ];
    }

    protected function hotel_rules()
    {
        return [
            'hotel_name' => 'required|string',
            'hotel_city' => 'required|string',
            'hotel_start' => 'required|date',
            'hotel_end' => 'required|date|after:hotel_start',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'mission.subject' => __('Purpose of the mission'),
            'mission.institution_id' => __('Institution'),
            'mission.om' => __('Mission order number'),
            'mission.conference' => __('Conference'),
            'mission.conf_amount' => __('Registration fee to be paid by the institution'),
            'mission.conf_currency' => __('Currency'),
            'mission.costs' => __('Mission with or without costs'),
            'mission.dest_country' => __('Destination'),
            'mission.dest_city' => __('City'),
            'mission.departure' => __('Departure'),
            'mission.from' => __('From your address'),
            'mission.return' => __('Return'),
            'mission.to' => __('To your address'),
            'mission.tickets' => __('Transport Tickets'),
            'mission.hotels' => __('Accomodation'),
            'mission.meal' => __('Meal'),
            'mission.taxi' => __('Taxi'),
            'mission.transport' => __('Public transport'),
            'mission.personal_car' => __('Private car'),
            'mission.rental_car' => __('Rental car'),
            'mission.parking' => __('Parking'),
            'mission.registration' => __('Conference registration fee'),
            'mission.accomodation' => __('Off-market accomodation'),
            'mission.others' => __('Other expenses'),
            'ticket_mode' => __('Travel mode'),
            'ticket_direction' => __('Direction'),
            'ticket_number' => __('Flight/Train No.'),
            'ticket_date' => __('Date'),
            'ticket_time' => __('Time'),
            'ticket_from' => __('City of departure'),
            'ticket_to' => __('City of arrival'),
            'hotel_name' => __('Name'),
            'hotel_city' => __('City'),
            'hotel_start' => __('start date'),
            'hotel_end' => __('end date'),
        ];
    }

    protected function messages()
    {
        return [
            'uploads.*.image' => __('The :filename file must be an image.'),
            'uploads.*.max' => __('The size of the :filename file cannot exceed :max kilobytes.'),
            'uploads.*.mimes' => __('The file :filename must be a file of type: :values.'),
            'uploads.*.mimetypes' => __('The file :filename must be a file of type: :values.'),
            'mission.om.required_if' => __('The :attribute field is required in order to complete the processing.'),
            'ticket_date.required' => __('The :attribute field is required.'),
            'ticket_time.required' => __('The :attribute field is required.'),
        ];
    }

    protected $listeners = ['refreshMission' => '$refresh'];

    public function mount($id = null)
    {
        $this->isAuthManager = auth()->user()->can('manage-users');

        if (is_null($id)) {
            $this->mission = $this->makeBlankMission();
        } else {
            $this->mission = Mission::findOrFail($id);
            $this->showWP = $this->mission->institution->wp;

            if (! auth()->user()->can('manage-users') && auth()->id() !== $this->mission->user_id) {
                abort(403);
            }
        }

        $this->reset(['uploads', 'modified', 'del_docs', 'programme']);
        $this->dispatchBrowserEvent('pondReset');
        $this->resetValidation();
        $this->statesUpdate();
    }

    public function init()
    {
        $this->mount($this->mission->id);
    }

    public function statesUpdate()
    {
        if ($this->mission->status === 'cancelled') {
            // Commande annulée, personne ne peut plus rien faire
            $this->disabled = true;
            $this->disabledStatuses = array_keys(Mission::STATUSES);

        } elseif (auth()->user()->can('manage-users')) {
            // Gestionnnaire
            if ($this->mission->user_id === auth()->id()) {
                // Le gestionnaire est aussi l'auteur de la commande

                if ($this->mission->status === 'draft') {
                    $this->disabled = false;
                    $this->disabledStatuses = array_diff(array_keys(Mission::STATUSES), ['draft', 'on-hold', 'cancelled']);
                } elseif ($this->mission->status === 'on-hold') {

                    $this->disabled = false;
                    if ($this->mission->managers->doesntContain('user_id', auth()->id())) {
                        $this->disabledStatuses = array_diff(array_keys(Mission::STATUSES), ['draft', 'on-hold', 'cancelled']);
                    } else {
                        $this->disabledStatuses = [];
                    }

                } elseif ($this->mission->status === 'in-progress') {
                    if ($this->mission->managers->doesntContain('user_id', auth()->id())) {
                        $this->disabled = true;
                        $this->disabledStatuses = array_keys(Mission::STATUSES);

                    } else {
                        $this->disabled = false;
                        $this->disabledStatuses = ['draft', 'on-hold'];
                    }

                } elseif ($this->mission->status === 'processed') {
                    if ($this->mission->managers->doesntContain('user_id', auth()->id())) {
                        $this->disabled = true;
                        $this->disabledStatuses = array_keys(Mission::STATUSES);

                    } else {
                        $this->disabled = false;
                        $this->disabledStatuses = array_keys(Mission::STATUSES);
                    }
                }

            } else {
                // Le gestionnaire n'est pas l'auteur de la commande

                if ($this->mission->managers->doesntContain('user_id', auth()->id())) {
                    // Le gestionnaire n'est pas associé à la commande, il ne peut rien faire sans s'associer
                    $this->disabled = true;
                    $this->disabledStatuses = array_keys(Mission::STATUSES);

                } elseif ($this->mission->status === 'draft') {
                    $this->disabled = true;
                    $this->disabledStatuses = array_keys(Mission::STATUSES);

                } elseif ($this->mission->status === 'on-hold') {
                    $this->disabled = false;
                    $this->disabledStatuses = ['draft'];

                } elseif ($this->mission->status === 'in-progress') {
                    $this->disabled = false;
                    $this->disabledStatuses = ['draft', 'on-hold'];

                } elseif ($this->mission->status === 'processed') {
                    // Une commande terminée ne peut plus changer de status
                    $this->disabled = false;
                    $this->disabledStatuses = array_keys(Mission::STATUSES);
                }
            }
        } else {
            // Utilisateur
            if (in_array($this->mission->status, ['draft', 'on-hold'])) {
                $this->disabled = false;
                $this->disabledStatuses = array_diff(array_keys(Mission::STATUSES), ['draft', 'on-hold', 'cancelled']);
            } else {
                // Une fois la commande soumise, l'utilisateur ne peut plus rien faire
                $this->disabled = true;
                $this->disabledStatuses = array_keys(Mission::STATUSES);
            }
        }
    }

    public function render()
    {
        return view('livewire.edit-mission')
            ->layoutData(['pageTitle' => __('Mission').' '.$this->mission->id]);
    }

    // Ajoute un document à la liste des documents à supprimer
    public function del_doc($id)
    {
        if ($this->disabled === true) {
            return;
        }

        if (! empty(Document::find($id)) && ! in_array($id, $this->del_docs)) {

            $this->del_docs[] = $id;
            $this->modified = true;

        }
    }

    // Associate current manager to Mission
    public function associate()
    {
        Manager::create([
            'user_id' => auth()->id(),
            'manageable_id' => $this->mission->id,
            'manageable_type' => Mission::class,
        ]);
        if ($this->mission->status === 'on-hold') {
            $this->mission->update(['status' => 'in-progress']);
            $user = User::findOrFail($this->mission->user_id);
            Mail::to($user)->send(new MissionStatusChange($this->mission, $user->name, auth()->user()->name));
        }
        $this->emit('refreshMission');
        $this->emit('refreshMessagerie');
        $this->init();
    }

    // Dissociate current manager from Mission if he's not the only one
    public function dissociate()
    {
        // Check if manager is not the only one
        if (count($this->mission->managers) > 1) {
            Manager::where('user_id', '=', auth()->id())
                ->where('manageable_type', '=', Mission::class)
                ->where('manageable_id', '=', $this->mission->id)
                ->delete();
            $this->emit('refreshMission');
            $this->init();
        }
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, array_keys($this->ticket_rules()))) {
            $this->validateOnly($propertyName, $this->ticket_rules());

        } elseif (in_array($propertyName, array_keys($this->hotel_rules()))) {
            $this->validateOnly($propertyName, $this->hotel_rules());

        } else {
            $this->validateOnly($propertyName);
            $this->modified = ! empty($this->mission->getDirty());
        }
    }

    public function updatedProgramme()
    {
        if ($this->programme) {
            $this->validateOnly('programme');
            $this->modified = true;
        }
    }

    public function updatedUploads()
    {
        if ($this->uploads) {

            $this->validateOnly('uploads.*');

            $this->modified = true;
        }
    }

    public function updatedMissionInstitutionId()
    {
        $this->showWP = Institution::find($this->mission->institution_id)->wp;
        ! $this->showWP && $this->validateOnly('mission.wp');
    }

    // Start Tickets

    public function close_ticket()
    {
        // Hide modal
        $this->showTicket = false;

        // Reset form
        $this->ticket_mode = '';
        $this->ticket_direction = '';
        $this->ticket_number = '';
        $this->ticket_date = null;
        $this->ticket_time = null;
        $this->ticket_from = '';
        $this->ticket_to = '';
        unset($this->ticket_id);
    }

    // Affiche le modal d'édition du ticket après en avoir initialisé les valeurs
    public function edit_ticket(int $id)
    {
        if ($this->disabled === true) {
            return;
        }

        // Get from json to array
        $tickets = $this->mission->tickets;

        // Control
        if ($id < 1 || $id > count($tickets)) {
            return;
        }

        // Initialize values
        $this->ticket_id = $id;
        $this->ticket_mode = isset($tickets[$id - 1]['ticket_mode']) ? $tickets[$id - 1]['ticket_mode'] : '';
        $this->ticket_direction = isset($tickets[$id - 1]['ticket_direction']) ? $tickets[$id - 1]['ticket_direction'] : '';
        $this->ticket_number = isset($tickets[$id - 1]['ticket_number']) ? $tickets[$id - 1]['ticket_number'] : '';
        $this->ticket_date = isset($tickets[$id - 1]['ticket_date']) ? $tickets[$id - 1]['ticket_date'] : null;
        $this->ticket_time = isset($tickets[$id - 1]['ticket_time']) ? $tickets[$id - 1]['ticket_time'] : null;
        $this->ticket_from = isset($tickets[$id - 1]['ticket_from']) ? $tickets[$id - 1]['ticket_from'] : '';
        $this->ticket_to = isset($tickets[$id - 1]['ticket_to']) ? $tickets[$id - 1]['ticket_to'] : '';

        // Show modal
        $this->showTicket = true;
    }

    public function del_ticket(int $id)
    {
        if ($this->disabled === true) {
            return;
        }

        $tickets = $this->mission->tickets;
        if ($id < 1 || $id > count($tickets)) {
            return;
        }
        if (isset($tickets[$id - 1])) {
            unset($tickets[$id - 1]);
        }
        $this->mission->tickets = array_values($tickets);
        $this->modified = true;
    }

    // Valide le formulaire et stocke le résultat en json dans la mission
    public function save_ticket()
    {
        // Validate current edited ticket
        $current_ticket = $this->validate($this->ticket_rules());

        // Get current tickets list
        $tickets = $this->mission->tickets;

        // New ticket or editing existing ticket, add to array
        if (! empty($this->ticket_id)) {
            $tickets[$this->ticket_id - 1] = $current_ticket;
        } else {
            $tickets[] = $current_ticket;
        }

        // Convert array to json
        $this->mission->tickets = $tickets;

        $this->modified = true;

        $this->close_ticket();
    }

    // End Tickets

    // Start Hotels

    public function close_hotel()
    {
        // Hide modal
        $this->showHotel = false;

        // Reset form
        $this->hotel_name = '';
        $this->hotel_city = '';
        $this->hotel_start = null;
        $this->hotel_end = null;
        unset($this->hotel_id);
    }

    // Affiche le modal d'édition de l'hotel après en avoir initialisé les valeurs
    public function edit_hotel(int $id)
    {
        if ($this->disabled === true) {
            return;
        }

        // Get from json to array
        $hotels = $this->mission->hotels;

        // Control
        if ($id < 1 || $id > count($hotels)) {
            return;
        }

        // Initialize values
        $this->hotel_id = $id;
        $this->hotel_name = isset($hotels[$id - 1]['hotel_name']) ? $hotels[$id - 1]['hotel_name'] : '';
        $this->hotel_city = isset($hotels[$id - 1]['hotel_city']) ? $hotels[$id - 1]['hotel_city'] : '';
        $this->hotel_start = isset($hotels[$id - 1]['hotel_start']) ? $hotels[$id - 1]['hotel_start'] : null;
        $this->hotel_end = isset($hotels[$id - 1]['hotel_end']) ? $hotels[$id - 1]['hotel_end'] : null;

        // Show modal
        $this->showHotel = true;
    }

    public function del_hotel(int $id)
    {
        if ($this->disabled === true) {
            return;
        }

        $hotels = $this->mission->hotels;
        if ($id < 1 || $id > count($hotels)) {
            return;
        }
        if (isset($hotels[$id - 1])) {
            unset($hotels[$id - 1]);
        }
        $this->mission->hotels = array_values($hotels);
        $this->modified = true;
    }

    // Valide le formulaire et stocke le résultat en json dans la mission
    public function save_hotel()
    {
        // Validate current edited hotel
        $current_hotel = $this->validate($this->hotel_rules());

        // Get current hotels list
        $hotels = $this->mission->hotels;

        // New hotel or editing existing hotel, add to array
        if (! empty($this->hotel_id)) {
            $hotels[$this->hotel_id - 1] = $current_hotel;
        } else {
            $hotels[] = $current_hotel;
        }

        // Convert array to json
        $this->mission->hotels = $hotels;

        $this->modified = true;

        $this->close_hotel();
    }

    // End Hotels

    public function makeBlankMission()
    {
        return Mission::make([
            'user_id' => Auth()->id(),
            'status' => 'draft',
            'dest_country' => 'FR',
            'conference' => false,
            'conf_currency' => 'EUR',
            'costs' => true,
            'from' => 'home',
            'to' => 'home',
            'tickets' => [],
            'hotels' => [],
            'taxi' => false,
            'transport' => false,
            'personal_car' => false,
            'rental_car' => false,
            'parking' => false,
            'registration' => false,
        ]);
    }

    public function save()
    {
        $creation = is_null($this->mission->id);

        //Force json encodage
        $this->mission->hotels = $this->mission->hotels;
        $this->mission->tickets = $this->mission->tickets;

        $this->withValidator(function (Validator $validator) {
            if ($validator->fails()) {
                $this->emitSelf('notify-error');
            }
        })->validate();

        $this->mission->save();

        if ($creation) {
            // Mise à jour de l'url à la création
            $this->emit('urlChange', route('edit-mission', $this->mission->id));
        }

        // Traitement des uploads

        // Create user documents directory if not exists
        if (! empty($this->programme) || ! empty($this->uploads)) {
            $path = 'docs/'.$this->mission->user_id.'/';
            Storage::makeDirectory($path);
        }

        // Sauvegarde du programme si présent
        if (! empty($this->programme)) {
            // Store file in directory
            $filename = $this->programme->storeAs('/'.$path, $this->programme->hashName());

            // Create file in BDD
            Document::create([
                'name' => Document::filter_filename($this->programme->getClientOriginalName()),
                'type' => 'programme',
                'size' => Storage::size($filename),
                'filename' => $this->programme->hashName(),
                'user_id' => $this->mission->user_id,
                'documentable_id' => $this->mission->id,
                'documentable_type' => Mission::class,
            ]);
        }

        // Sauvegarde des fichiers ajoutés
        if (! empty($this->uploads)) {

            foreach ($this->uploads as $file) {
                // Store file in directory
                $filename = $file->storeAs('/'.$path, $file->hashName());

                // Create file in BDD
                Document::create([
                    'name' => Document::filter_filename($file->getClientOriginalName()),
                    'type' => 'document',
                    'size' => Storage::size($filename),
                    'filename' => $file->hashName(),
                    'user_id' => $this->mission->user_id,
                    'documentable_id' => $this->mission->id,
                    'documentable_type' => Mission::class,
                ]);
            }
        }

        // Reset des composants filepond
        if (! empty($this->programme) || ! empty($this->uploads)) {
            $this->dispatchBrowserEvent('pondReset');
        }

        // Suppression des fichiers à supprimer
        foreach ($this->del_docs as $id) {

            Document::findOrFail($id)->delete();

        }

        $this->reset(['uploads', 'modified', 'del_docs', 'programme']);
        $this->emit('refreshMission');
        $this->emitSelf('notify-saved');
        $this->statesUpdate();

        if ($this->mission->status === 'draft' && auth()->user()->cannot('manage-users')) {
            $this->showInformationMessage = 'submit-mission';
        }

        if (array_key_exists('status', $this->mission->getChanges()) && $this->mission->status !== 'draft') {
            // Envoi de mail lors d'un changement de status uniquement
            $user = User::findOrFail($this->mission->user_id);
            Mail::to($user)->send(new MissionStatusChange($this->mission, $user->name, auth()->user()->name));
        }
    }
}
