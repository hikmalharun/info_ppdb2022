<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Daftar extends CI_Controller
{

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
    }

    public function get_client_ip_env()
    {
        $ipaddress  = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN IP Address';

        return $ipaddress;
    }

    public function get_os()
    {
        $os_platform = $_SERVER['HTTP_USER_AGENT'];
        return $os_platform;
    }

    public function getting_browser()
    {
        $browser = '';
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Netscape'))
            $browser = 'Netscape';
        else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox'))
            $browser = 'Firefox';
        else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome'))
            $browser = 'Chrome';
        else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera'))
            $browser = 'Opera';
        else if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
            $browser = 'Internet Explorer';
        else
            $browser = 'Other';
        return $browser;
    }

    public function get_macaddress()
    {
        ob_start();
        system('getmac');
        $Content = ob_get_contents();
        ob_clean();
        return substr($Content, strpos($Content, '\\') - 20, 17);
    }

    public function zonasi()
    {
        //Generate token
        $max        = 6;
        $token      = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, $max);
        $nama       = $this->input->post('nama');
        $ttl        = $this->input->post('ttl');
        $sekolah    = $this->input->post('sekolah');
        $jalur      = "Zonasi";
        $nohp       = $this->input->post('nohp');
        $time_add   = date_default_timezone_set('Asia/Jakarta');
        $time_add   = date('d-m-Y H:i:s');
        $ip         = $this->get_client_ip_env();
        $browser    = $this->getting_browser();
        $os         = $this->get_os();
        $mac        = $this->get_macaddress();
        $kk         = $_FILES['kk'];
        $akta       = $_FILES['akta'];

        $config['upload_path']      = './assets/file/';
        $config['allowed_types']    = 'pdf';
        $config['max_size']         = 1024;
        $config['encrypt_name']     = true;

        $this->load->library('upload', $config);

        if (!$this->upload->initialize($config)) {
            $this->session->set_flashdata('pesan', '
                <div class="alert alert-danger alert-dismissible  fade show" role="alert">
                    <p style="font-size: 14px; color: #000;">
                        File gagal diupload, silahkan coba lagi!
                    </p>
                </div>
            ',);
            redirect('index.php/home/#portfolio');
        } else {
            if ($kk != '') {
                $this->upload->do_upload('kk');
                $file1 = $this->upload->data('file_name');
            } else {
                $this->session->set_flashdata('pesan', '
                    <div class="alert alert-danger alert-dismissible  fade show" role="alert">
                        <p style="font-size: 14px; color: #000;">
                            File kartu keluarga gagal diupload, silahkan coba lagi!
                        </p>
                    </div>
                ',);
                redirect('index.php/home/#portfolio');
            }

            if ($akta != '') {
                $this->upload->do_upload('akta');
                $file2 = $this->upload->data('file_name');
            } else {
                $this->session->set_flashdata('pesan', '
                    <div class="alert alert-danger alert-dismissible  fade show" role="alert">
                        <p style="font-size: 14px; color: #000;">
                            File akta lahir gagal diupload, silahkan coba lagi!
                        </p>
                    </div>
                ',);
                redirect('index.php/home/#portfolio');
            }

            $data = array(
                'token' => $token,
                'nama' => $nama,
                'ttl' => $ttl,
                'sekolah' => $sekolah,
                'jalur' => $jalur,
                'nohp' => $nohp,
                'time_add' => $time_add,
                'ip' => $ip,
                'browser' => $browser,
                'os' => $os,
                'mac' => $mac,
                'kk' => $file1,
                'akta' => $file2,
                'file1' => '$file1',
                'file2' => '$file2'
            );

            $this->db->insert('pendaftar', $data);
            $this->session->set_userdata($data);
            $this->session->set_flashdata('pesan', '
                    <div class="alert alert-success alert-dismissible  fade show" role="alert">
                        <p style="font-size: 14px; color: #000;">
                            Data dengan Token : <b>' . $this->session->userdata('token') . '</b> berhasil disimpan dengan rician sebagai berikut :
                        </p>
                        <div class="row" style="margin-bottom:-15 px; font-size: 14px;">
                            <div class="col-sm-2" style="height: 50px;">
                                <b>Nama</b>
                                <p class="text-dark fw-bold">' . $this->session->userdata('nama') . '</p>.
                            </div>
                            <div class="col-sm-2" style="height: 50px;">
                                <b>Tempat, Tanggal Lahir</b>
                                <p class="text-dark fw-bold">' . $this->session->userdata('ttl') . '</p>.
                            </div>
                            <div class="col-sm-2" style="height: 50px;">
                                <b>Sekolah</b>
                                <p class="text-dark fw-bold">' . $this->session->userdata('sekolah') . '</p>.
                            </div>
                            <div class="col-sm-2" style="height: 50px;">
                                <b>Jalur</b>
                                <p class="text-dark fw-bold">' . $this->session->userdata('jalur') . '</p>.
                            </div>
                            <div class="col-sm-2" style="height: 50px;">
                                <b>Pada Waktu</b>
                                <p class="text-dark fw-bold">' . $this->session->userdata('time_add') . '</p>.
                            </div>
                            <div class="col-sm-2" style="height: 50px;">
                                <b>Nomor Handphone</b>
                                <p class="text-dark fw-bold">' . $this->session->userdata('nohp') . '</p>.
                            </div>
                        </div>
                        <div style="font-size: 14px; color: #000; cursor: pointer;">
                            <span data-bs-toggle="modal" data-bs-target="#file1">Lihat Kartu Keluarga</span> | <span data-bs-toggle="modal" data-bs-target="#file2">Lihat Akta Lahir</span>
                        </div>
                    </div>
                ',);
            redirect('index.php/home/#portfolio');
        }
    }
}
