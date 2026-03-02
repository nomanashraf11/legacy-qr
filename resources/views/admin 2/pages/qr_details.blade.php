@extends('admin.layout.master')
@section('title', 'Living Leagacy | Dashboard')
@section('content')
    {{-- <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-6">
                    <div class="page-title-box">
                        <h4 class="page-title">QrCode Details</h4>
                    </div>
                </div>
                <div class="col-6">
                    <div class="page-title-box">
                        <button class="mt-2 btn btn-danger deleteProfile" id="{{ $link->uuid }}">Delete Profile</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <h1>Profile Details</h1>
                <p>{{ $link->profile->name }}</p>
                <p>{{ $link->profile->dob }}</p>
                <p>{{ $link->profile->dod }}</p>
                <p> Profile</p>
                <img width="100" height="100"
                    src="{{ asset('images/profile/profile_pictures/' . $link->profile->profile_picture) }}" alt="">
                <p> Cover</p>
                <img width="100" height="100"
                    src="{{ asset('images/profile/cover_pictures/' . $link->profile->cover_picture) }}" alt="">
                <p>{{ $link->profile->facebook }}</p>
                <p>{{ $link->profile->instagram }}</p>
                <p>{{ $link->profile->twitter }}</p>
                <p>{{ $link->profile->spotify }}</p>
                <p>{{ $link->profile->youtube }}</p>
                <p>{{ $link->profile->bio }}</p>
            </div>
            <div class="col">
                <h1>Photos</h1>
                @foreach ($link->profile->photos as $photo)
                    @if (pathinfo($photo->image, PATHINFO_EXTENSION) === 'mp4')
                        <video width="320" height="240" controls>
                            <source src="{{ asset('images/profile/photos/' . $photo->image) }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        <img width="100" height="100" src="{{ asset('images/profile/photos/' . $photo->image) }}"
                            alt="">
                    @endif
                @endforeach
            </div>

            <div class="col">
                <h1>Timelines</h1>
                @foreach ($link->profile->timelines as $timeline)
                    <p>Title : {{ $timeline->title }}</p>
                    <p>Date : {{ $timeline->date }}</p>
                    <p>Description : {{ $timeline->description }}</p>
                    <br>
                @endforeach
            </div>
            <div class="col">
                <h1>Tributes</h1>
                @foreach ($link->profile->tributes as $tribute)
                    <p>Description : {{ $tribute->description }}</p>
                    @if ($tribute->image)
                        <img width="60" height="60" src="{{ asset('images/profile/tributes/' . $tribute->image) }}"
                            alt="">
                    @endif
                    <br>
                @endforeach
            </div>
        </div>
    </div> --}}

    <div class="content">
        <div class="container-fluid pe-lg-4">
            @if ($link->profile)
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-flex align-items-center justify-content-between">
                            <h4 class="page-title">Profile</h4>

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-2">Person's Bio</h4>
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th>Name</th>
                                            <td>{{ $link->profile->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date of Birth</th>
                                            <td>{{ $link->profile->dob }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date of Death</th>
                                            <td>{{ $link->profile->dod }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="mb-2">User Details</h4>
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th>Name</th>
                                            <td>{{ $link->localUser->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $link->localUser->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone</th>
                                            <td>{{ $link->localUser->phone }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h4>Profile Photo</h4>
                                <img src="{{ asset('images/profile/profile_pictures/' . $link->profile->profile_picture) }}"
                                    alt="" style="max-height: 200px; width:200px;"
                                    class="img-fluid object-fit-cover rounded">
                                <br>
                            </div>
                            <div class="col-md-8">
                                <h4>Cover Photo</h4>
                                <img src="{{ asset('images/profile/cover_pictures/' . $link->profile->cover_picture) }}"
                                    alt="" class="img-fluid object-fit-cover border rounded border"
                                    style="max-height: 200px; height:200px; width:200px;">
                                <br>

                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div class="card">
                    <div class="card-body">
                        <p>Gallery Photos</p>
                        <div class="row">
                            @foreach ($link->photos as $photo)
                                @if ($photo->image)
                                    @if (pathinfo($photo->image, PATHINFO_EXTENSION) === 'mp4')
                                        <div class="col-md-3 m-2 video-w gallery-container text-center">
                                            <video class="w-100 h-100 rounded object-fit-cover" controls>
                                                <source src="{{ asset('images/profile/photos/' . $photo->image) }}"
                                                    type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                            <br>
                                            <form class="position-absolute top-0 end-0 pe-2"
                                                action="{{ route('deletePhotoFromProfile', $photo->uuid) }}" method="post">
                                                @csrf
                                                <button type="submit" class="delete-button border rounded-circle ">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="col-md-3 m-2 gallery-container border rounded text-center">
                                            <img src="{{ asset('images/profile/photos/' . $photo->image) }}" alt=""
                                                class="img-fluid rounded w-100 h-100 object-fit-cover">
                                            <br>
                                            <form class="position-absolute top-0 end-0"
                                                action="{{ route('deletePhotoFromProfile', $photo->uuid) }}"
                                                method="post">
                                                @csrf
                                                <button type="submit" class="delete-button border rounded-circle">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                @endif
                            @endforeach

                            {{-- <div class="col-md-3 m-2 text-center">
                            <img src="https://via.placeholder.com/400" alt="" class="img-fluid rounded">
                            <br>
                            <a href="#" class="text-danger">remove</a>
                        </div>
                        <div class="col-md-3 m-2 text-center">
                            <img src="https://via.placeholder.com/400" alt="" class="img-fluid rounded">
                            <br>
                            <a href="#" class="text-danger">remove</a>
                        </div>
                        <div class="col-md-3 m-2 text-center">
                            <img src="https://via.placeholder.com/400" alt="" class="img-fluid rounded">
                            <br>
                            <a href="#" class="text-danger">remove</a>
                        </div>
                        <div class="col-md-3 m-2 text-center">
                            <img src="https://via.placeholder.com/400" alt="" class="img-fluid rounded">
                            <br>
                            <a href="#" class="text-danger">remove</a>
                        </div> --}}

                        </div>

                    </div>
                </div>
                <br>
                <br>
                <h4 class="fw-bold mb-4">Timeline</h4>

                <div class="card">
                    <div class="card-body">
                        <ul>
                            @foreach ($link->timelines as $timeline)
                                <li>
                                    <p class="fw-bold mb-0">{{ $timeline->title }}
                                        <span class="fst-itslics fw-normal ps-3 small">{{ $timeline->date }}</span>
                                    </p>
                                    <p class="small">{{ $timeline->description }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <br>
                <br>
                <h4 class="fw-bold mb-4">Tributes</h4>

                <div class="card">
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Text</th>
                                    <th scope="col">Photo</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($link->tributes as $tribute)
                                    <tr>
                                        <th scope="row">{{ $tribute->created_at }}</th>
                                        <td>{{ $tribute->name }}</td>
                                        <td>{{ $tribute->description }}</td>
                                        <td>
                                            @if ($tribute->image)
                                                <img src="{{ asset('images/profile/tributes/' . $tribute->image) }}"
                                                    alt="" class="img-fluid" width="50px">
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.delete.tribute', $tribute->uuid) }}"
                                                method="post">
                                                @csrf
                                                <button class="text-danger bg-transparent mt-1 border-0 p-0" type="submit">
                                                    <i class="mdi mdi-trash-can-outline h4"></i>
                                                </button>
                                        </td>
                                        </form>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <h4>No Profile Data Added Yet</h4>
            @endif
            <div class="text-end">
                <button class="btn btn-outline-danger h4 gap-3 deleteProfile" id="{{ $link->uuid }}">
                    Reset <i class="mdi mdi-trash-can-outline"></i>
                </button>
            </div>
        </div>

    </div>

    @include('admin.pages.modals.deleteProfile')
@endsection
@push('scripts')
    <script src="{{ asset('js/link.js') }}"></script>
@endpush
