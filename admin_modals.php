<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="?add_user=1">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="fullname" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                            <option value="moderator">Moderator</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editUserForm">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="user_id" id="editUserId">
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="fullname" id="editFullname" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" id="editUsername" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="editEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="editRole">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                            <option value="moderator">Moderator</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="editStatus">
                            <option value="active">Active</option>
                            <option value="banned">Banned</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-white">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Photo Detail Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Photo Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <img src="" id="photoDetailImage" class="img-fluid rounded" style="max-height: 400px;">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h5 id="photoDetailTitle"></h5>
                        <p id="photoDetailDescription" class="text-muted"></p>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Uploaded by:</strong> <span id="photoDetailAuthor"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Rating:</strong> <span id="photoDetailRating"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Date:</strong> <span id="photoDetailDate"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong> <span id="photoDetailStatus"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Views:</strong> <span id="photoDetailViews">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Story Detail Modal -->
<div class="modal fade" id="storyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Story Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h4 id="storyDetailTitle"></h4>
                <div class="mb-3">
                    <strong>By:</strong> <span id="storyDetailAuthor"></span>
                    <span class="text-muted ms-2" id="storyDetailDate"></span>
                </div>
                <div class="rating mb-3" id="storyDetailRating"></div>
                <div class="card mb-3">
                    <div class="card-body">
                        <p id="storyDetailContent" class="mb-0"></p>
                    </div>
                </div>
                <div id="storyDetailExperience"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Edit User Modal Handler
    document.getElementById('editUserModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const userData = JSON.parse(button.getAttribute('data-user'));
        
        document.getElementById('editUserId').value = userData.id;
        document.getElementById('editFullname').value = userData.fullname;
        document.getElementById('editUsername').value = userData.username;
        document.getElementById('editEmail').value = userData.email;
        document.getElementById('editRole').value = userData.role;
        document.getElementById('editStatus').value = userData.status;
    });
    
    // Photo Detail Modal Handler
    document.getElementById('photoModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const photoData = JSON.parse(button.getAttribute('data-photo'));
        
        document.getElementById('photoDetailImage').src = photoData.image;
        document.getElementById('photoDetailTitle').textContent = photoData.title;
        document.getElementById('photoDetailDescription').textContent = photoData.description;
        document.getElementById('photoDetailAuthor').textContent = photoData.fullname + ' (@' + photoData.username + ')';
        document.getElementById('photoDetailDate').textContent = new Date(photoData.created_at * 1000).toLocaleDateString();
        document.getElementById('photoDetailStatus').textContent = photoData.status;
        document.getElementById('photoDetailViews').textContent = photoData.views || 0;
        
        // Create rating stars
        let ratingHTML = '';
        for(let i = 1; i <= 5; i++) {
            ratingHTML += `<i class="fas fa-star ${i <= photoData.rating ? 'text-warning' : 'text-secondary'}"></i>`;
        }
        document.getElementById('photoDetailRating').innerHTML = ratingHTML;
    });
    
    // Story Detail Modal Handler
    document.getElementById('storyModal').addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const storyData = JSON.parse(button.getAttribute('data-story'));
        
        document.getElementById('storyDetailTitle').textContent = storyData.title;
        document.getElementById('storyDetailAuthor').textContent = storyData.fullname + ' (@' + storyData.username + ')';
        document.getElementById('storyDetailDate').textContent = new Date(storyData.created_at * 1000).toLocaleDateString();
        document.getElementById('storyDetailContent').textContent = storyData.content;
        
        // Create rating stars
        let ratingHTML = '';
        for(let i = 1; i <= 5; i++) {
            ratingHTML += `<i class="fas fa-star ${i <= storyData.rating ? 'text-warning' : 'text-secondary'}"></i>`;
        }
        document.getElementById('storyDetailRating').innerHTML = ratingHTML;
        
        // Show experience if exists
        if(storyData.experience) {
            document.getElementById('storyDetailExperience').innerHTML = `
                <div class="alert alert-info">
                    <strong>Experience Type:</strong> ${storyData.experience}
                </div>
            `;
        } else {
            document.getElementById('storyDetailExperience').innerHTML = '';
        }
    });
</script>