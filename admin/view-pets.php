<?php
include('../connect.php');

// Function to fetch pet details
function getPetDetails($petId, $conn) {
    try {
        $sql = "SELECT 
                p.pet_id,
                p.pet_name,
                p.pet_breed,
                p.pet_size,
                p.pet_age,
                p.pet_gender,
                p.pet_description,
                p.pet_special_instruction,
                p.pet_vaccination_status,
                p.pet_vaccination_date_administered,
                p.pet_vaccination_date_expiry,
                p.pet_picture,
                c.c_first_name,
                c.c_last_name
            FROM 
                pet p
            JOIN 
                customer c ON p.customer_id = c.c_id
            WHERE 
                p.pet_id = :pet_id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':pet_id', $petId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}

// If AJAX request is made to fetch pet details
if (isset($_GET['fetch_pet']) && isset($_GET['pet_id'])) {
    $petId = $_GET['pet_id'];
    $petDetails = getPetDetails($petId, $conn);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($petDetails);
    exit;
}
?>

<!-- VIEW PET MODAL -->
<div class="modal" id="viewPetModal" data-bs-backdrop="false" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" id="view-pet">
            <div class="modal-header" id="mheader">
                <h5 class="modal-title" id="petModalLabel">PET INFORMATION</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="image-container">
                            <div class="image-container text-center">
                                <img id="pet_picture" src="Profile-Pics/pet_icon.png" class="img-fluid rounded" style="max-height: 200px; width: auto; object-fit: contain;">
                            </div>
                        </div>
                    </div>
                                            
                    <div class="col-md-9">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Owner</label>
                                <p id="pet-owner" class="form-control-static"></p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Pet Name</label>
                                <p id="pet-name" class="form-control-static"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Breed</label>
                                <p id="pet-breed" class="form-control-static"></p>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Pet Size</label>
                                <p id="pet-size" class="form-control-static"></p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Age</label>
                                <p id="pet-age" class="form-control-static"></p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Gender</label>
                            <p id="pet-gender" class="form-control-static"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <p id="pet-description" class="form-control-static"></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Special Instructions</label>
                            <p id="pet_special_instruction" class="form-control-static"></p>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Vaccination Status</label>
                                <p id="pet_vaccination_status" class="form-control-static"></p>
                            </div>
                        
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Date Administered</label>
                                <p id="pet_vaccination_date_administered" class="form-control-static"></p>
                            </div>
                        
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Expiry Date</label>
                                <p id="pet_vaccination_date_expiry" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
