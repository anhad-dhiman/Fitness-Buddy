<?php
require 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if profile exists for this user
$profileStmt = $conn->prepare("SELECT * FROM profile WHERE user_id = :user_id");
$profileStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$profileStmt->execute();
$profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

// create a profile record if it doesn't exist
if (!$profile) {
    $createProfileStmt = $conn->prepare("INSERT INTO profile (user_id) VALUES (:user_id)");
    $createProfileStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $createProfileStmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateFields = [];
    $params = [':user_id' => $userId];

    if (isset($_POST['fitness_goals'])) {
        $updateFields[] = "fitness_goals = :fitness_goals";
        $params[':fitness_goals'] = implode(',', $_POST['fitness_goals']);
    }
    if (!empty($_POST['experience_level'])) {
        $updateFields[] = "experience_level = :experience_level";
        $params[':experience_level'] = $_POST['experience_level'];
    }
    if (isset($_POST['workout_types'])) {
        $updateFields[] = "workout_types = :workout_types";
        $params[':workout_types'] = implode(',', $_POST['workout_types']);
    }
    if (isset($_POST['availability'])) {
        $updateFields[] = "availability = :availability";
        $params[':availability'] = implode(',', $_POST['availability']);
    }
    if (!empty($_POST['gym_location'])) {
        $updateFields[] = "gym_location = :gym_location";
        $params[':gym_location'] = $_POST['gym_location'];
    }
    if (isset($_POST['share_location'])) {
        $updateFields[] = "share_location = :share_location";
        $params[':share_location'] = 1;
    } else {
        $updateFields[] = "share_location = :share_location";
        $params[':share_location'] = 0;
    }
    if (!empty($_POST['bio'])) {
        $updateFields[] = "bio = :bio";
        $params[':bio'] = $_POST['bio'];
    }
    if (!empty($_POST['membership_tier'])) {
        $updateFields[] = "membership_tier = :membership_tier";
        $params[':membership_tier'] = $_POST['membership_tier'];
    }

    // Handle file upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = "./uploads/profile_images/";
        $fileName = time() . "_" . basename($_FILES["profile_picture"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Allowed file types
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
                $updateFields[] = "profile_picture = :profile_picture";
                $params[':profile_picture'] = $targetFilePath;
            }
        }
    }

    if (!empty($updateFields)) {
        $query = "UPDATE profile SET " . implode(', ', $updateFields) . " WHERE user_id = :user_id";
        $updateStmt = $conn->prepare($query);
        $updateStmt->execute($params);
    }

    // If the profile is not yet completed, mark it as completed
    if ($user['profile_completed'] == 0) {
        $completeStmt = $conn->prepare("UPDATE users SET profile_completed = 1 WHERE id = :user_id");
        $completeStmt->bindParam(':user_id', $userId);
        $completeStmt->execute();
    }

    header("Location: index.php?profile_updated=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - Fitness Buddy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/profileSetup.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: salmon;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">Fitness Buddy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="profileSetup.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Matches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Forum</a>
                    </li>
                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="form-container">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="profile-section">
                <h5>Complete Your Profile</h5>
                <div class="profile-picture-placeholder" id="profile-pic-container" style="cursor: pointer;">
                    <?php if (!empty($profile['profile_picture'])): ?>
                        <img src="<?php echo $profile['profile_picture']; ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="bi bi-camera"></i> Add Photo
                    <?php endif; ?>
                </div>
                <input type="file" name="profile_picture" id="profile-pic-input" accept="image/*"
                    style="display: none;">
            </div>

            <div class="profile-section">
                <h5>Fitness Goals (Select all that apply)</h5>
                <div class="d-flex flex-wrap">
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="fitness_goals[]" value="weight_loss"
                            id="weight_loss">
                        <label class="form-check-label" for="weight_loss">Weight Loss</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="fitness_goals[]" value="muscle_building"
                            id="muscle_building">
                        <label class="form-check-label" for="muscle_building">Muscle Building</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="fitness_goals[]" value="flexibility"
                            id="flexibility">
                        <label class="form-check-label" for="flexibility">Flexibility</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="fitness_goals[]" value="athleticism"
                            id="athleticism">
                        <label class="form-check-label" for="athleticism">Athleticism</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="fitness_goals[]" value="endurance"
                            id="endurance">
                        <label class="form-check-label" for="endurance">Endurance</label>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h5>Experience Level:</h5>
                <div class="d-flex">
                    <div class="form-check me-4">
                        <input class="form-check-input" type="radio" name="experience_level" value="beginner"
                            id="beginner" checked>
                        <label class="form-check-label" for="beginner">Beginner</label>
                    </div>
                    <div class="form-check me-4">
                        <input class="form-check-input" type="radio" name="experience_level" value="intermediate"
                            id="intermediate">
                        <label class="form-check-label" for="intermediate">Intermediate</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="experience_level" value="advanced"
                            id="advanced">
                        <label class="form-check-label" for="advanced">Advanced</label>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h5>Preferred Workout Types:</h5>
                <div class="d-flex flex-wrap">
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="workout_types[]" value="cardio"
                            id="cardio">
                        <label class="form-check-label" for="cardio">Cardio</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="workout_types[]" value="weightlifting"
                            id="weightlifting">
                        <label class="form-check-label" for="weightlifting">Weightlifting</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="workout_types[]" value="pilates"
                            id="pilates">
                        <label class="form-check-label" for="pilates">Pilates</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="workout_types[]" value="yoga" id="yoga">
                        <label class="form-check-label" for="yoga">Yoga</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="workout_types[]" value="crossfit"
                            id="crossfit">
                        <label class="form-check-label" for="crossfit">Crossfit</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="workout_types[]" value="other" id="other">
                        <label class="form-check-label" for="other">Other</label>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h5>Availability:</h5>
                <div class="d-flex flex-wrap">
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="availability[]" value="weekday_morning"
                            id="weekday_morning">
                        <label class="form-check-label" for="weekday_morning">Weekday Morning</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="availability[]" value="weekday_evening"
                            id="weekday_evening">
                        <label class="form-check-label" for="weekday_evening">Weekday Evenings</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="availability[]" value="weekend_morning"
                            id="weekend_morning">
                        <label class="form-check-label" for="weekend_morning">Weekend Morning</label>
                    </div>
                    <div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="availability[]" value="weekend_evening"
                            id="weekend_evening">
                        <label class="form-check-label" for="weekend_evening">Weekend Evenings</label>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Gym Location:</h5>
                    <div>
                        <span class="me-2">Share location with matches?</span>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="share_location" id="share_yes" value="1">
                            <label class="form-check-label" for="share_yes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="share_location" id="share_no" value="0"
                                checked>
                            <label class="form-check-label" for="share_no">No</label>
                        </div>
                    </div>
                </div>
                <select class="gym-location" name="gym_location">
                    <option value="">Select your gym location</option>
                    <option value="downtown">Downtown Fitness</option>
                    <option value="eastside">Eastside Gym</option>
                    <option value="westend">West End Athletics</option>
                    <option value="northside">North Side Fitness Center</option>
                    <option value="southpark">South Park Gym</option>
                    <option value="other">Other (Specify in Bio)</option>
                </select>
            </div>

            <div class="profile-section">
                <h5>Bio (150 max):</h5>
                <textarea class="form-control" name="bio" rows="4" maxlength="150"
                    placeholder="Tell potential workout partners about yourself..."></textarea>
            </div>

            <div class="membership-section">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle w-100 text-start" type="button"
                        id="membershipDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Update Membership
                    </button>
                    <div class="dropdown-menu w-100 p-0" aria-labelledby="membershipDropdown">
                        <div class="tier-options">
                            <div class="tier-box" id="freeTierBox">
                                <h5>Free Tier</h5>
                                <ul class="tier-features">
                                    <li>Basic profile creation</li>
                                    <li>Limited matching</li>
                                    <li>Basic consistency tracker</li>
                                    <li>Forum access (view only)</li>
                                </ul>
                                <div class="form-check mt-2">
                                    <input class="form-check-input tier-select" type="radio" name="membership_tier"
                                        id="free_tier" value="free" checked>
                                    <label class="form-check-label" for="free_tier">Select</label>
                                </div>
                            </div>
                            <div class="tier-box" id="premiumTierBox">
                                <h5>Premium Tier</h5>
                                <ul class="tier-features">
                                    <li>All tier 1 features</li>
                                    <li>Advanced profile creation</li>
                                    <li>Advanced matching filters</li>
                                    <li>Ad-free experience</li>
                                    <li>Full forum participation</li>
                                </ul>
                                <div class="form-check mt-2">
                                    <input class="form-check-input tier-select" type="radio" name="membership_tier"
                                        id="premium_tier" value="premium">
                                    <label class="form-check-label" for="premium_tier">Select</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="dashboard.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-save">Save</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('profile-pic-container').addEventListener('click', function () {
            document.getElementById('profile-pic-input').click();
        });

        document.getElementById('profile-pic-input').addEventListener('change', function () {
            if (this.files && this.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.width = 100;
                    img.style.borderRadius = '50%';

                    var container = document.getElementById('profile-pic-container');
                    container.innerHTML = '';
                    container.appendChild(img);
                }

                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>

    <script>
        // Handle membership tier selection
        const freeTierBox = document.getElementById('freeTierBox');
        const premiumTierBox = document.getElementById('premiumTierBox');
        const freeTierRadio = document.getElementById('free_tier');
        const premiumTierRadio = document.getElementById('premium_tier');

        // Initial selection
        updateTierSelection();

        freeTierRadio.addEventListener('change', updateTierSelection);
        premiumTierRadio.addEventListener('change', updateTierSelection);

        function updateTierSelection() {
            if (freeTierRadio.checked) {
                freeTierBox.classList.add('selected-tier');
                premiumTierBox.classList.remove('selected-tier');
            } else {
                premiumTierBox.classList.add('selected-tier');
                freeTierBox.classList.remove('selected-tier');
            }
        }

        // Character counter for bio
        const bioTextarea = document.querySelector('textarea[name="bio"]');
        bioTextarea.addEventListener('input', function () {
            if (this.value.length > 150) {
                this.value = this.value.substring(0, 150);
            }
        });
    </script>
</body>

</html>